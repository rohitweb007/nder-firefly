<?php

use Carbon\Carbon as Carbon;

class AccountEventHandler {

  public function makeBDP($account, $date) {
//    var_dump($event);exit;
//    $account      = $event['account'];
//    $date         = $event['date'];
    $start        = new Carbon($account->date);
    $startbalance = floatval($account->balance);
    $return = null;
    while ($date >= $start) {
      // calculate the account's balance for this day and save it in the database.
      // if it's already there, skip it:
      $workbalance = $account->balancedatapoints()->where('date', '=', $date->format('Y-m-d'))->first();
      if (is_null($workbalance)) {
        // calculate it:
        $tr_sum           = floatval($account->transactions()->where('date', '<=', $date->format('Y-m-d'))->sum('amount'));
        $away_sum         = floatval($account->transfersfrom()->where('date', '<=', $date->format('Y-m-d'))->sum('amount')) * -1;
        $here_sum         = floatval($account->transfersto()->where('date', '<=', $date->format('Y-m-d'))->sum('amount'));
        $result           = $startbalance + $tr_sum + $away_sum + $here_sum;
        $data             = new Balancedatapoint();
        $data->date       = $date->format('Y-m-d');
        $data->balance    = $result;
        $data->account_id = $account->id;
        $validator        = Validator::make($data->toArray(), Balancedatapoint::$rules);
        if (!$validator->fails()) {
          $data->save();
          if($date == new Carbon($account->date)) {
            $return = $data;
          }
        }
      }
      $date->subDay();
    }
    return $data;
  }
}

Event::listen('account.makeBDP', 'AccountEventHandler@makeBDP');

