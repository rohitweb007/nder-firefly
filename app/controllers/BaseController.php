<?php

use Holmes\Holmes; 
use Carbon\Carbon as Carbon;
class BaseController extends Controller {

  /**
   * Setup the layout used by the controller.
   *
   * @return void
   */
  protected function setupLayout() {
    if (!is_null($this->layout)) {
      $this->layout = View::make($this->layout);
    }
  }

  public static function _determinePeriod() {
    // get the period from the session:
    $sessionPeriod = Session::get('period');
    $today         = new Carbon('now');
    $today->modify('midnight');
    if (is_null($sessionPeriod)) {
      // new period: today at midnight:
      $sessionPeriod = new Carbon('now');
      $sessionPeriod->modify('midnight');
      Session::put('period', $sessionPeriod);
    }

    // if there is something in the URL:
    if (!is_null(Request::segment(2)) && !is_null(Request::segment(3))) {
      if (intval(Request::segment(2)) > 1000) { // crude check for year.
        $string = '1 ' . Request::segment(3) . ' ' . Request::segment(2);
        $date   = new Carbon('now');
        try {
          $date = new Carbon($string);
        } catch (Exception $e) {

        }
        // check if matches today:
        if ($date > $today) {
          // in the future:
          $date->modify('first day of this month');
        } else if ($date < $today) {
          // in the past:
          $date->modify('last day of this month');
        } else if ($date == $today) {
          $date = clone $today;
        } else {
          Log::error('No catch for date');
        }
        Session::put('period', $date);
      }
    }
  }

  /**
   * Returns date of very first transaction or
   * transfer
   */
  public static function getFirst($accountID = null) {
    if (!is_null($accountID)) {
      $account = Auth::user()->accounts()->find($accountID);
      $first   = cacheKey('getFirst', $account->id);
    } else {
      $account = null;
      $first   = cacheKey('getFirst');
    }

    if (Cache::has($first)) {
      return Cache::get($first);
    }
    // build the query:
    $transf_query = Auth::user()->transfers()->orderBy('date', 'ASC');
    $transa_query = Auth::user()->transactions()->orderBy('date', 'ASC');

    if (!is_null($account)) {
      $transa_query->where('account_id', '=', $account->id);
      $transf_query->where(function($query) use ($account) {
                $query->where('account_from', '=', $account->id);
                $query->orWhere('account_to', '=', $account->id);
              });
    }

    $transfer    = $transf_query->first();
    $transaction = $transa_query->first();

    $transf_date = is_null($transfer) ? new Carbon('now') : new Carbon($transfer->date);
    $transa_date = is_null($transaction) ? new Carbon('now') : new Carbon($transaction->date);
    $result      = $transf_date <= $transa_date ? $transf_date : $transa_date;
    Cache::put($first, $result, 5000);
    return $result;
  }

  /**
   * Returns date of very last transaction or
   * transfer
   */
  public static function getLast($accountID = null) {
    if (!is_null($accountID)) {
      $account = Auth::user()->accounts()->find($accountID);
      $last    = cacheKey('getLast', $account->id);
    } else {
      $account = null;
      $last    = cacheKey('getLast');
    }

    if (Cache::has($last)) {
      return Cache::get($last);
    }
    // build the query:
    $transf_query = Auth::user()->transfers()->orderBy('date', 'DESC');
    $transa_query = Auth::user()->transactions()->orderBy('date', 'DESC');

    if (!is_null($account)) {
      $transa_query->where('account_id', '=', $account->id);
      $transf_query->where(function($query) use ($account) {
                $query->where('account_from', '=', $account->id);
                $query->orWhere('account_to', '=', $account->id);
              });
    }

    $transfer    = $transf_query->first();
    $transaction = $transa_query->first();

    $transf_date = is_null($transfer) ? new Carbon('now') : new Carbon($transfer->date);
    $transa_date = is_null($transaction) ? new Carbon('now') : new Carbon($transaction->date);
    $result      = max($transf_date, $transa_date);
    Cache::put($last, $result, 5000);
    return $result;
  }
}