<?php

use Carbon\Carbon as Carbon;

class Account extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'name'           => 'required|between:1,50',
      'balance'        => 'required|numeric',
      'date'           => 'required|date|after:1950-01-01',
      'fireflyuser_id' => 'required|exists:users,id',
  );

  public function fireflyuser() {
    return $this->belongsTo('Fireflyuser');
  }

  public function balance(Carbon $date = null) {
    $date  = is_null($date) ? new Carbon(Session::get('period')->format('Y-m-d')) : $date;
    $today = new Carbon();
    $start = new Carbon($this->date);
    if ($date > $today) {
      $date = $today;
    }
    if ($date < $start) {
      return floatval($this->balance);
    }

    $balance = $this->balancedatapoints()->where('date', '=', $date->format('Y-m-d'))->first();

    if (is_null($balance)) {
      // trigger balance point for this day.
      $result = Event::fire('account.makeBDP', array('account' => $this, 'date'    => $date));
      if (isset($result[0])) {
        $balance = $result[0];
      } else {
        App::abort(500);
      }
    }
    return $balance->balance;
  }

  public function transfersfrom() {
    return $this->hasMany('Transfer', 'account_from');
  }

  public function transfersto() {
    return $this->hasMany('Transfer', 'account_to');
  }

  /**
   * Tries to predict how much you'll spend
   * on this day of the month.
   * @param DateTime $date
   */
  public function predict(Carbon $date = null) {
    $date = is_null($date) ? Session::get('period') : $date;

    /**
     * select alle transacties, op vandaag (dag > 24)
     * en maand is niet deze (month != 6)
     * en flikker ze op een hoop (sum amount).
     * Gedeeld door aantal maanden bezig nu (5) == antwoord.
     */
    $transactions = $this->transactions()->
            where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '=', $date->format('d'))->
            where(DB::Raw('DATE_FORMAT(`date`,"%m")'), '!=', $date->format('m'))->
            where('amount', '<', 0)->
            get(array('amount'));
    $sum          = 0;
    $first        = BaseController::getFirst($this->id);
    $diff         = $first->diff($date);
    foreach ($transactions as $t) {
      $sum += floatval($t->amount) * -1;
    }
    $months = ($diff->y * 12) + $diff->m;

    return ($months > 0 ? ($sum / $months) : $sum);
  }

  public function transactions() {
    return $this->hasMany('Transaction');
  }

  public function balancedatapoints() {
    return $this->hasMany('Balancedatapoint');
  }

}