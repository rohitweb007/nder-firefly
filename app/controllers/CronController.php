<?php

use Carbon\Carbon as Carbon;

class CronController extends BaseController {

  public function accountCharts() {
    $accounts = Account::get();
    $AC       = new AccountController;
    $date     = new Carbon('today');
    $count    = 0;

    foreach ($accounts as $account) {
      Auth::loginUsingId($account->fireflyuser_id);
      // remove cached entry:
      $key = cacheKey('Account', 'homeOverviewChart', $account->id, $date);
      Cache::forget($key);
      $AC->homeOverviewChart($account->id, $date);
      $count++;
      Auth::logout();
    }
    return 'Regenerated ' . $count . ' account charts.';
  }

  public function budgetCharts() {
    $budgets = Budget::get();
    $BC      = new BudgetController;
    $date    = new Carbon('today');
    $count   = 0;

    foreach ($budgets as $budget) {
      Auth::loginUsingId($budget->fireflyuser_id);

      // remove cached entry:
      $key = cacheKey('Budget', 'homeOverviewChart', $budget->id, $date);
      Cache::forget($key);
      $BC->homeOverviewChart($budget->id, $date);
      $count++;
      Auth::logout();
    }
    return 'Regenerated ' . $count . ' budget charts.';
  }

}