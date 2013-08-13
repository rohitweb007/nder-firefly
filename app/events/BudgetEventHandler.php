<?php

use Carbon\Carbon as Carbon;

class BudgetEventHandler {

  public function updateBudgetPrediction($event) {
    $event->name = Crypt::decrypt($event->name);

    // remove all budget prediction points, if any
    $event->budgetpredictionpoints()->delete();
    $similar = array();

    // get all similar budgets from the past:
    $budgets = Auth::user()->budgets()->where('date', '<=', $event->date)->get();
    foreach ($budgets as $budget) {
      $budget->name = Crypt::decrypt($budget->name);
      if ($budget->name == $event->name) {
        $similar[] = $budget->id;
      }
    }
    if (count($similar) > 0) {
      // get all transactions for these budgets:
      $amounts      = array();
      $transactions = Auth::user()->transactions()->orderBy('date', 'DESC')->where('onetime', '=', 0)->whereIn('budget_id', $similar)->get();
      foreach ($transactions as $t) {
        $date          = new Carbon($t->date);
        $day           = intval($date->format('d'));
        $amounts[$day] = isset($amounts[$day]) ? $amounts[$day] + floatval($t->amount) * -1 : floatval($t->amount) * -1;
      }
      // then make sure it's "average".
      foreach ($amounts as $day => $amount) {
        // save as budget prediction point.
        $bpp            = new Budgetpredictionpoint;
        $bpp->budget_id = $event->id;
        $bpp->amount    = ($amount / count($similar));
        $bpp->day       = $day;
        $bpp->save();
      }
    }
  }

  public function subscribe($events) {
    $events->listen('eloquent.saved: Budget', 'BudgetEventHandler@updateBudgetPrediction');
  }

}

$BudgetHandler = new BudgetEventHandler();
Event::subscribe($BudgetHandler);