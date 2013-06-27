<?php

class Account extends Eloquent {

  protected $guarded = array('id');
  public static $rules  = array(
      'name'           => 'required|between:1,50',
      'balance'        => 'required|numeric',
      'date'           => 'required|date|after:1950-01-01',
      'fireflyuser_id' => 'required|exists:users,id',
  );

  public function fireflyuser() {
    return $this->belongsTo('Fireflyuser');
  }

  public function balance(DateTime $date = null) {
    // default to the period date:
    $date = is_null($date) ? clone Session::get('period') : $date;


    // calculate and cache this account's balance on the date given.
    $start = floatval($this->balance);


    // add and substract all transactions:
    $tr_sum = floatval($this->transactions()->where('date','<=',$date->format('Y-m-d'))->sum('amount'));
    //Log::error('balance equation: ' . $date->format('Y-m-d'));

    // substract all transfers away from this account:
    $away_sum = floatval($this->transfersfrom()->where('date','<=',$date->format('Y-m-d'))->sum('amount')) * -1;

    // add all transfers TO this account
    $here_sum = floatval($this->transfersto()->where('date','<=',$date->format('Y-m-d'))->sum('amount'));

    return $start + $tr_sum + $away_sum + $here_sum;
  }

  public function transfersfrom() {
    return $this->hasMany('Transfer','account_from');
  }

  public function transfersto() {
    return $this->hasMany('Transfer','account_to');
  }



  public function transactions() {
    return $this->hasMany('Transaction');
  }

}