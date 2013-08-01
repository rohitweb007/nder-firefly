<?php

use Carbon\Carbon as Carbon;

class PageController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function predictionChart() {
    return View::make('pages.prediction');
  }

  public function progressPage() {
    // let's find budgets with "sisters", budgets with the same name. Then, get some graphs going on.
    $budgets = Auth::user()->budgets()->orderBy('date', 'DESC')->get();
    $list    = array();
    $last    = array();
    $period  = clone Session::get('period');
    $period->modify('first day of this month');
    foreach ($budgets as $budget) {
      $name  = Crypt::decrypt($budget->name);
      $count = isset($list[$name]['count']) ? $list[$name]['count'] + 1 : 1;

      $date = new Carbon($budget->date);
      // the budget who's ID we use is either the last one or the
      // one with this period's timeframe.
      // the last one:
      if ($date == $period) {
        $last[$name] = $budget->toArray();
      }

      $budgetArray = array('id'    => $budget->id, 'count' => $count);
      $list[$name] = $budgetArray;
    }

    foreach ($list as $name => $data) {
      if ($data['count'] < 2) {
        unset($list[$name]);
      } else {

        if (isset($last[$name])) {
          $thisLast             = $last[$name];
          // add some extra information for the overview.
          $budget               = Auth::user()->budgets()->find($thisLast['id']);
          $list[$name]['spent'] = $budget->spent();
          $list[$name]['avg'] = -1;
        } else {
          $list[$name]['spent'] = -1;
          $list[$name]['avg'] = -1;
        }
      }
    }



    return View::make('pages.progress')->with('budgets', $list);
  }

  public function compare() {
    // get a list of all months:
    $months = array();
    $first  = BaseController::getFirst();
    $first->modify('first day of this month midnight');

    $today = new Carbon('now');
    $today->modify('first day of this month midnight');

    $prev = clone $today;
    $prev->sub(new DateInterval('P2D'));
    $prev->modify('first day of this month midnight');

    while ($first <= $today) {
      $index          = $first->format('Y-m-d');
      $months[$index] = $first->format('F Y');
      $first->add(new DateInterval('P1M'));
    }

    // account list:
    $accs     = Auth::user()->accounts()->get();
    $accounts = array(0 => '(all accounts)');
    foreach ($accs as $acc) {
      $accounts[intval($acc->id)] = Crypt::decrypt($acc->name);
    }
    $account = Setting::getSetting('defaultCheckingAccount');

    return View::make('pages.compare')->with('months', $months)->with('thisMonth', $today)->with('prevMonth', $prev)
                    ->with('account', $account)->with('accounts', $accounts);
  }

}