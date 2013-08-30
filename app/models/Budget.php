<?php

use Carbon\Carbon as Carbon;

class Budget extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'fireflyuser_id' => 'required|exists:users,id|integer',
      'name'           => 'required|between:1,255',
      'date'           => 'required|after:1900-01-01|before:2038-01-01',
      'amount'         => 'required|numeric|between:0,65535'
  );

  public function transactions() {
    return $this->hasMany('Transaction');
  }

  public function budgetpredictionpoints() {
    return $this->hasMany('Budgetpredictionpoint');
  }

  public function transfers() {
    return $this->hasMany('Transfer');
  }

  public function left(DateTime $date = null) {
    $date = is_null($date) ? clone Session::get('period') : $date;
    return $this->amount - $this->spent($date);
  }

  /**
   * How much money have you spent on this budget? TODO
   * @param DateTime $date
   * @return int
   */
  public function spent(DateTime $date = null) {
    $date         = is_null($date) ? Session::get('period') : $date;
    $transactions = floatval($this->transactions()->where('date', '<=', $date->format('Y-m-d'))->sum('amount'));

    return ($transactions * -1);
  }

  /**
   * Calculates the expected amount you'll spend the
   * rest of the month.
   * @param DateTime $date
   */
  public function expected(DateTime $date = null) {
    $date         = is_null($date) ? Session::get('period') : $date;
    $name         = strlen($this->name) > 20 ? Crypt::decrypt($this->name) : $this->name;
    // we'll need to grab a certain subset of transactions and work through them (encryption be
    // a bitch).
    $transactions = Auth::user()->transactions()->
                    where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '!=', $date->format('m-Y'))->
                    whereNotNull('budget_id')->
                    where('onetime', '=', 0)->
                    where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '>', $date->format('d'))->get();
    $sum          = 0;


    foreach ($transactions as $transaction) {
      $budget = $transaction->budget()->first();
      $bname  = Crypt::decrypt($budget->name);
      if ($name == $bname) {
        // part of this budget's past.
        $sum += ($transaction->amount * -1);
      }
    }

    $oldest = BaseController::getFirst();
    $diff   = $oldest->diff($date);
    $months = $diff->m + ($diff->y * 12);
    return $months == 0 ? $sum : ($sum / $months);
  }

  /**
   * Tries to predict how much you'll spend
   * on this day of the month.
   * @param DateTime $date
   */
  public function predict(Carbon $date = null) {
    $date    = is_null($date) ? Session::get('period') : $date;
    $name    = Crypt::decrypt($this->name);
    $similar = array();
    // find likewise budgets:
    foreach (Auth::user()->budgets()->get() as $b) {
      $b->name = Crypt::decrypt($b->name);
      if ($b->name == $name && $b->id != $this->id) {
        $similar[] = intval($b->id);
      }
    }
    if (count($similar) == 0) {
      return 0;
    }
    $similar[] = $this->id;
    /**
     * select alle transacties, na vandaag (dag > 24)
     * en maand is niet deze (month != 6)
     * en flikker ze op een hoop (sum amount).
     * Gedeeld door aantal maanden bezig nu (5) == antwoord.
     */
    $total     = Auth::user()->transactions()->
            where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '=', $date->format('d'))->
            //where(DB::Raw('DATE_FORMAT(`date`,"%m")'),'!=',$date->format('m'))->
            whereIn('budget_id', $similar)->
            where('amount', '<', 0)->
            sum('amount');
    $oldest    = BaseController::getFirst();
    $diff      = $oldest->diff($date);
    $months = $diff->m + (12* $diff->y);

    return $months != 0 ? (($total * -1) / $months) : $total * -1;
  }

  /**
   * How much money to spend on this day. TODO
   * @param DateTime $date
   */
  public function advice(DateTime $date = null) {
    $date     = is_null($date) ? Session::get('period') : $date;
    $left     = $this->left($date);
    $daysleft = intval($date->format('t')) - intval($date->format('j'));

    return $daysleft == 0 ? $left : ($left / $daysleft);
  }

  public static function getHomeOverview() {
    $budgets = Auth::user()->budgets()->
                    leftJoin('transactions', 'transactions.budget_id', '=', 'budgets.id')->
                    groupBy('budgets.id')->
                    where(DB::Raw('DATE_FORMAT(`budgets`.`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->get(
            array('budgets.id', 'budgets.predicted', 'budgets.name', 'budgets.amount', DB::Raw('SUM(`transactions`.`amount`) AS `spent`')));
    $data    = array();
    foreach ($budgets as $b) {
      $budget             = array(
          'id'        => intval($b->id),
          'name'      => Crypt::decrypt($b->name),
          'predicted' => floatval($b->predicted),
          'spent'     => floatval($b->spent),
          'left'      => floatval($b->amount) + floatval($b->spent),
          'amount'    => floatval($b->amount),
      );
      $budget['overflow'] = $budget['predicted'] > $budget['left'];
      $data[]             = $budget;
    }
    return $data;
  }

}