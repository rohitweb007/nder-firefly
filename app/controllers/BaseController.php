<?php

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
    $today         = new DateTime('now');
    $today->modify('midnight');
    if (is_null($sessionPeriod)) {
      // new period: today at midnight:
      $sessionPeriod = new DateTime('now');
      $sessionPeriod->modify('midnight');
      Session::put('period', $sessionPeriod);
    }

    // if there is something in the URL:
    if (!is_null(Request::segment(2)) && !is_null(Request::segment(3))) {
      if (intval(Request::segment(2)) > 1000) { // crude check for year.
        $string = '1 ' . Request::segment(3) . ' ' . Request::segment(2);
        $date   = new DateTime('now');
        try {
          $date = new DateTime($string);
        } catch (Exception $e) {

        }
        // check if matches today:
        if ($date > $today) {
          // in the future:
          $date->modify('last day of this month');
        } else if ($date < $today) {
          $date->modify('first day of this month');
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
  public static function getFirst() {
    $firstTransaction = Auth::user()->transactions()->orderBy('date', 'ASC')->first();
    if (!is_null($firstTransaction)) {
      $firstTransactionDate = new DateTime($firstTransaction->date);
      unset($firstTransaction);
    } else {
      $firstTransactionDate = new DateTime('now');
    }

    $firstTransfer     = Auth::user()->transfers()->orderBy('date', 'ASC')->first();

    if (!is_null($firstTransfer)) {
      $firstTransferDate = new DateTime($firstTransfer->date);
      unset($firstTransaction);
    } else {
      $firstTransferDate = new DateTime('now');
    }
    return min($firstTransactionDate, $firstTransferDate);
  }

}