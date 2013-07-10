<?php
class PageController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function predictionChart() {
    return View::make('pages.prediction');
  }

  public function compare() {
    // get a list of all months:
    $months = array();
    $first = BaseController::getFirst();
    $first->modify('first day of this month midnight');

    $today = new DateTime('now');
    $today->modify('first day of this month midnight');

    $prev = clone $today;
    $prev->sub(new DateInterval('P2D'));
    $prev->modify('first day of this month midnight');

    while($first <= $today) {
      $index = $first->format('Y-m-d');
      $months[$index] = $first->format('F Y');
      $first->add(new DateInterval('P1M'));
    }

    // account list:
    $accs = Auth::user()->accounts()->get();
    $accounts = array(0 => '(all accounts)');
    foreach($accs as $acc) {
      $accounts[intval($acc->id)] = Crypt::decrypt($acc->name);
    }
    $account = Setting::getSetting('defaultCheckingAccount');

    return View::make('pages.compare')->with('months',$months)->with('thisMonth',$today)->with('prevMonth',$prev)
          ->with('account',$account)->with('accounts',$accounts);
  }


}