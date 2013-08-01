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
    $date    = is_null($date) ? new Carbon(Session::get('period')->format('Y-m-d')) : $date;
    $today = new Carbon();
    $start        = new Carbon($this->date);
    if($date > $today) {
      $date = $today;
    }
    if($date < $start) {
      return floatval($this->balance);
    }

    $balance = $this->balancedatapoints()->where('date', '=', $date->format('Y-m-d'))->remember(1440)->first();

    if (!is_null($balance)) {
      return $balance->balance;
    } else {
      // terug rekenen tot de start van account en balance points maken:

      $startbalance = floatval($this->balance);
      $workdate     = clone $date;
      while ($workdate >= $start) {
        // calculate the account's balance for this day and save it in the database.
        // if it's already there, skip it:
        $workbalance = $this->balancedatapoints()->remember(1440)->where('date', '=', $workdate->format('Y-m-d'))->first();
        if (is_null($workbalance)) {
          // calculate it:
          $tr_sum = floatval($this->transactions()->remember(1440)->where('date', '<=', $workdate->format('Y-m-d'))->sum('amount'));
          $away_sum = floatval($this->transfersfrom()->remember(1440)->where('date', '<=', $workdate->format('Y-m-d'))->sum('amount')) * -1;
          $here_sum = floatval($this->transfersto()->remember(1440)->where('date', '<=', $workdate->format('Y-m-d'))->sum('amount'));
          $result   = $startbalance + $tr_sum + $away_sum + $here_sum;
          $data = new Balancedatapoint();
          $data->date = $workdate->format('Y-m-d');
          $data->balance = $result;
          $data->account_id = $this->id;
          $data->save();
        }
        $workdate->subDay();
      }
      // then, we should / MUST have today's balance.
      $today = $this->balancedatapoints()->remember(1440)->where('date', '=', $date->format('Y-m-d'))->first();
      if(is_null($today)) {
        return App::abort(500);
      } else {
        return floatval($today->balance);
      }
    }
  }

  public function balanceOld(DateTime $date = null) {
    $key = cacheKey('accountBalance', $this->id, $date);
    if (Cache::has($key)) {
      return Cache::get($key);
    }
    // default to the period date:
    $date = is_null($date) ? clone Session::get('period') : $date;

    // calculate and cache this account's balance on the date given.
    $start = floatval($this->balance);


    // add and substract all transactions:
    $tr_sum = floatval($this->transactions()->where('date', '<=', $date->format('Y-m-d'))->sum('amount'));
    //Log::error('balance equation: ' . $date->format('Y-m-d'));
    // substract all transfers away from this account:
    $away_sum = floatval($this->transfersfrom()->where('date', '<=', $date->format('Y-m-d'))->sum('amount')) * -1;

    // add all transfers TO this account
    $here_sum = floatval($this->transfersto()->where('date', '<=', $date->format('Y-m-d'))->sum('amount'));
    $result   = $start + $tr_sum + $away_sum + $here_sum;
    Cache::put($key, $result, 5000);
    return $result;
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
  public function predict(DateTime $date = null) {
    $date = is_null($date) ? Session::get('period') : $date;

    /**
     * select alle transacties, na vandaag (dag > 24)
     * en maand is niet deze (month != 6)
     * en flikker ze op een hoop (sum amount).
     * Gedeeld door aantal maanden bezig nu (5) == antwoord.
     */
    $total  = Auth::user()->transactions()->
            where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '=', $date->format('d'))->
            where(DB::Raw('DATE_FORMAT(`date`,"%m")'), '!=', $date->format('m'))->
            where('amount', '<', 0)->
            sum('amount');
    $oldest = BaseController::getFirst();
    $diff   = $oldest->diff($date);

    return (($total * -1) / $diff->m);
  }

  public function transactions() {
    return $this->hasMany('Transaction');
  }

  public function balancedatapoints() {
    return $this->hasMany('Balancedatapoint');
  }

}