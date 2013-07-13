<?php

class ChartController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function predictionChart() {
    // in order to predict the future, we look at the past.
    //$baseAccount = ?;
    //$startBalance = ?;
    $setting = Auth::user()->settings()->where('name', '=', 'defaultAmount')->first();
    $balance = intval(Crypt::decrypt($setting->value));
    $account = Auth::user()->accounts()->orderBy('id', 'ASC')->first();
    $key     = cacheKey('prediction');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'day',
                  'label' => 'Day of the month',
                  'type'  => 'string',
                  'p'     => array('role' => 'domain')
              ),
              array(
                  'id'    => 'actualbalance',
                  'label' => 'Current balance',
                  'type'  => 'number',
                  'p'     => array('role' => 'data')),
              array(
                  'id'    => 'predictedbalance',
                  'label' => 'Predicted balance',
                  'type'  => 'number',
                  'p'     => array('role' => 'data')
              ),
              array(
                  'type' => 'number',
                  'p'    => array('role' => 'interval')
              ),
              array(
                  'type' => 'number',
                  'p'    => array('role' => 'interval'))
          ),
          'rows' => array()
      );


      // set the data array:
      // some working vars:
      $first     = BaseController::getFirst();
      $today     = new DateTime('now');
      $today->modify('first day of this month');
      $chartdate = new DateTime('now');
      $chartdate->modify('first day of this month');
      $index     = 0;

      $specificAmount = Auth::user()->settings()->where('name', '=', 'monthlyAmount')->where('date', '=', $today->format('Y-m-d'))->first();
      if ($specificAmount) {
        $balance = intval(Crypt::decrypt($specificAmount->value));
      }

      // loop over each day of the month:
      for ($i = 1; $i <= intval($today->format('t')); $i++) {
        $current = clone $first;
        $day     = $i - 1;
        if ($day > 0) {
          $current->add(new DateInterval('P' . $day . 'D'));
        }
        // the sum for this day of the month:
        $sums = array();
        // loop over each month:
        $min  = 1000000;
        $max  = 0;
        while ($current < $today) {
          //echo $current->format('Y-m-d') . ': ';
          $transaction_sum = Auth::user()->transactions()->where('amount', '<', 0)->where('onetime', '=', 0)->where('date', '=', $current->format('Y-m-d'))->sum('amount');
          $transfer_sum    = Auth::user()->transfers()->where('countasexpense', '=', 1)->where('ignoreprediction', '=', 0)->where('date', '=', $current->format('Y-m-d'))->sum('amount');

          $daysum = (floatval($transaction_sum) * -1) + floatval($transfer_sum);
          $sums[] = $daysum;
//          echo 'transactions: ' . mf($transaction_sum) . ', ';
//          echo 'transfers: ' . mf($transfer_sum);
          $min    = $daysum < $min ? $daysum : $min;
          $max    = $daysum > $max ? $daysum : $max;




          $current->add(new DateInterval('P1M'));
//          echo '<br>';
        }

        if (count($sums) == 0) {
          $avg = 0;
        } else if (count($sums) == 1) {
          $avg = array_sum($sums);
        } else {
          $avg = array_sum($sums) / count($sums);
        }

        $data['rows'][$index]['c'][0]['v'] = $chartdate->format('j F');
        $data['rows'][$index]['c'][1]['v'] = $account->balance($chartdate); // actual balance
        $data['rows'][$index]['c'][2]['v'] = $balance - $avg; // predicted balance
        $data['rows'][$index]['c'][3]['v'] = $balance - $max; // predicted max expenses.
        $data['rows'][$index]['c'][4]['v'] = $balance - $min; // predicted max expenses.
        $balance                           = $balance - $avg;



        $chartdate->add(new DateInterval('P1D'));
        $index++;
//        echo 'Avg voor deze dag: ' . mf($avg) . '<br>';
//        echo 'Min is ' . mf($min) . '<br>';
//        echo 'Max is ' . mf($max) . '<br>';
//        echo '<br>';
      }
      Cache::put($key, $data, 1440);
    }
//    echo '<pre>';
//    print_r($data);
    return Response::json($data);
  }

  public function showOverExpendingCategories() {
    $key = cacheKey('overExpendingCategories', Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    } else {
      $categories = Auth::user()->categories()->get();
      $data       = array(
          'cols' => array(
              array(
                  'id'    => 'Category',
                  'label' => 'Category',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'avg',
                  'label' => 'Spent too much',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'spent',
                  'label' => 'Spent so far',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'judgement',
                  'label' => 'Judgement',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'spentmore',
                  'label' => 'Spent on average',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );
      $collection = array();

      foreach ($categories as $category) {
        $avg_spent      = $category->averagespending();
        $spent          = $category->spent();
        $category->name = Crypt::decrypt($category->name);
        if ($avg_spent > 0) {
          if ($avg_spent < $spent) {
            $current    = array();
            // overspent as part of average:
            $spentpct   = 100 - (($avg_spent / $spent) * 100);
            $spentindex = round($spentpct / 10, 0);
            if ($spentindex == 0) {
              $descr = 'Overspent < 10%';
            } else {
              $descr = 'Overspent ~' . ($spentindex * 10) . '%';
            }

            // 0: Naam van bolletje.
            // 1: Verticale as (hoger is hoger)
            // 2: Horizontale as (hoger is verder naar rechts
            // 3: Kleur(groep)
            // 4: grootte van bolletje.

            $current['c'][0]['v'] = $category->name;
            $current['c'][1]['v'] = $spent - $avg_spent;
            $current['c'][2]['v'] = $spent;
            $current['c'][3]['v'] = $descr;
            $current['c'][4]['v'] = $avg_spent;


            $current['spentindex'] = $spentindex;
            $collection[]          = $current;
          }
        }
      }
      $tmp = array();
      foreach ($collection as &$ma) {
        $tmp[] = &$ma['spentindex'];
      }
      array_multisort($tmp, $collection);
      $index = 0;
      foreach ($collection as $c) {
        $data['rows'][$index]['c'] = $c['c'];
        $index++;
      }
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  public function showCategoriesByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));
      $key   = cacheKey('cba', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }
      $categories = Auth::user()->categories()->get();
      $records    = array();
      foreach ($categories as $cat) {
        $cat->name    = Crypt::decrypt($cat->name);
        // find out the expenses for each category:
        $trans_spent  = floatval($cat->transactions()->where('amount', '<', 0)->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
        $trans_earned = floatval($cat->transactions()->where('amount', '>', 0)->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $transf       = floatval($cat->transfers()->where('account_from', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
        $transf += floatval($cat->transfers()->where('account_to', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $records[]    = array(
            'category' => $cat->name,
            'spent'    => $trans_spent,
            'earned'   => $trans_earned,
            'moved'    => $transf
        );
      }
      // everything *outside* of the categories:
      $trans_spent  = floatval($account->transactions()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $trans_earned = floatval($account->transactions()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      $transf       = floatval($account->transfersfrom()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $transf += floatval($account->transfersto()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));


      array_unshift($records, array(
          'category' => 'Outside of categories',
          'spent'    => $trans_spent,
          'earned'   => $trans_earned,
          'moved'    => $transf
      ));


      // klopt wie ein busje!
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'cat',
                  'label' => 'Category',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'spent',
                  'label' => 'Spent',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'earned',
                  'label' => 'Earned',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'moved',
                  'label' => 'Moved',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($records as $r) {
        if (!($r['spent'] == 0 && $r['moved'] == 0)) {
          $data['rows'][$index]['c'][0]['v'] = $r['category'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['earned'];
          $data['rows'][$index]['c'][3]['v'] = $r['moved'];
          $index++;
        }
      }
      Cache::put($key, $data, 1440);
      return Response::json($data);
    }
  }

  public function showMovesByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {

      $results = array();
      $start   = new DateTime(Input::get('start'));
      $end     = new DateTime(Input::get('end'));
      $key     = cacheKey('mba', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }
      $transfersAwayFromHere = $account->transfersfrom()->where('transfers.date', '>=', $start->format('Y-m-d'))->where('transfers.date', '<=', $end->format('Y-m-d'))
              ->groupBy('accounts.name')
              ->leftJoin('accounts', 'accounts.id', '=', 'transfers.account_to')
              ->get(array('accounts.name', DB::Raw('SUM(`amount`) as `sum`')));

      $transfersToHere = $account->transfersto()->where('transfers.date', '>=', $start->format('Y-m-d'))->where('transfers.date', '<=', $end->format('Y-m-d'))
              ->groupBy('accounts.name')
              ->leftJoin('accounts', 'accounts.id', '=', 'transfers.account_from')
              ->get(array('accounts.name', DB::Raw('SUM(`amount`) as `sum`')));

      foreach ($transfersAwayFromHere as $tr) {
        $name = Crypt::decrypt($tr->name);
        if (!isset($results[$name])) {
          $results[$name] = array('name' => $name, 'to'   => 0, 'from' => 0);
        }
        $results[$name]['to'] += floatval($tr->sum);
      }
      foreach ($transfersToHere as $tr) {
        $name = Crypt::decrypt($tr->name);
        if (!isset($results[$name])) {
          $results[$name] = array('name' => $name, 'to'   => 0, 'from' => 0);
        }
        $results[$name]['from'] += floatval($tr->sum);
      }


      // klopt wie ein busje!
      $data  = array(
          'cols' => array(
              array(
                  'id'    => 'account',
                  'label' => 'Account',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'to',
                  'label' => 'Moved from ' . Crypt::decrypt($account->name),
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'from',
                  'label' => 'Moved to ' . Crypt::decrypt($account->name),
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'balance',
                  'label' => 'Balance',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );
      $index = 0;
      foreach ($results as $x) {
        $data['rows'][$index]['c'][0]['v'] = $x['name'];
        $data['rows'][$index]['c'][1]['v'] = $x['to'];
        $data['rows'][$index]['c'][2]['v'] = $x['from'];
        $data['rows'][$index]['c'][3]['v'] = $x['from'] - $x['to'];

        $index++;
      }
      Cache::put($key, $data, 1440);
      return Response::json($data);
    }
  }

  public function showBudgetsByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $key = cacheKey('bba', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }

      $start_first = clone $start;
      $end_first   = clone $end;
      $start_first->modify('first day of this month');
      $end_first->modify('first day of this month');


      // all budgets + stuff outside budgets should match this!
      $budgets = Auth::user()->budgets()->orderBy('date', 'DESC')->orderBy('amount', 'DESC')->where('date', '>=', $start_first->format('Y-m-d'))->where('date', '<=', $end_first->format('Y-m-d'))->get();
      $records = array();
      foreach ($budgets as $budget) {
        $budget->name = Crypt::decrypt($budget->name);
        $date         = new DateTime($budget->date);
        // find out the expenses for each budget:
        $trans_earned = floatval($budget->transactions()->where('amount', '>', 0)->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $trans_spent  = floatval($budget->transactions()->where('amount', '<', 0)->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;


        // find the earnings for this budget.
        // find how much we have moved for this budget:
        $transf = floatval($budget->transfers()->where('account_from', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
        $transf += floatval($budget->transfers()->where('account_to', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));

        $records[] = array(
            'budget' => $budget->name . ' (' . $date->format('F Y') . ')',
            'spent'  => $trans_spent,
            'earned' => $trans_earned,
            'moved'  => $transf
        );
      }
      // everything *outside* of the budgets:
      $outside_trans_earned = floatval($account->transactions()->where('amount', '>', 0)->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      $outside_trans_spent  = floatval($account->transactions()->where('amount', '<', 0)->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $outside_transf       = floatval($account->transfersfrom()->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $outside_transf += floatval($account->transfersto()->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));


      array_unshift($records, array(
          'budget' => 'Outside of budgets',
          'spent'  => $outside_trans_spent,
          'earned' => $outside_trans_earned,
          'moved'  => $outside_transf
      ));



      // klopt wie ein busje!
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'budget',
                  'label' => 'Budget',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'spent',
                  'label' => 'Spent',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'earned',
                  'label' => 'Earned',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'moved',
                  'label' => 'Moved',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($records as $r) {
        if (!($r['spent'] == 0 && $r['moved'] == 0)) {
          $data['rows'][$index]['c'][0]['v'] = $r['budget'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['earned'];
          $data['rows'][$index]['c'][3]['v'] = $r['moved'];
          $index++;
        }
      }
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  public function showTransactionsByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $key = cacheKey('transba', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }
      $trans = $account->transactions()->orderBy('date', 'DESC')->
              where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->
              get();

      $ct   = array(); // category temp
      $at   = array(); // account temp
      $bt   = array(); // budget temp
      $bet  = array(); // beneficiary temp
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'date',
                  'label' => 'Date',
                  'type'  => 'date',
              ),
              array(
                  'id'    => 'descr',
                  'label' => 'Description',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'amount',
                  'label' => 'Amount',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'budget',
                  'label' => 'Budget',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'category',
                  'label' => 'Category',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'beneficiary',
                  'label' => 'Beneficiary',
                  'type'  => 'string',
              ),
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($trans as $t) {

        // save acc. name:
        if (!isset($at[intval($t->account_id)])) {
          $at[intval($t->account_id)] = Crypt::decrypt($t->account()->first()->name);
        }
        // get budget and save
        if (!is_null($t->budget_id) && !isset($bt[intval($t->budget_id)])) {
          $bt[intval($t->budget_id)] = Crypt::decrypt($t->budget()->first()->name);
        }

        // get ben and save
        if (!is_null($t->beneficiary_id) && !isset($bet[intval($t->beneficiary_id)])) {
          $bet[intval($t->beneficiary_id)] = Crypt::decrypt($t->beneficiary()->first()->name);
        }

        // get cat and save
        if (!is_null($t->category_id) && !isset($ct[intval($t->category_id)])) {
          $ct[intval($t->category_id)] = Crypt::decrypt($t->category()->first()->name);
        }
        $date                              = new DateTime($t->date);
        $month                             = intval($date->format('n')) - 1;
        $year                              = intval($date->format('Y'));
        $day                               = intval($date->format('d'));
        $strDate                           = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
        $data['rows'][$index]['c'][0]['v'] = $strDate;
        $data['rows'][$index]['c'][1]['v'] = Crypt::decrypt($t->description);
        $data['rows'][$index]['c'][2]['v'] = floatval($t->amount);
        $data['rows'][$index]['c'][3]['v'] = (is_null($t->budget_id) ? null : $bt[intval($t->budget_id)]);
        $data['rows'][$index]['c'][4]['v'] = (is_null($t->beneficiary_id) ? null : $bet[intval($t->beneficiary_id)]);
        $data['rows'][$index]['c'][5]['v'] = (is_null($t->category_id) ? null : $ct[intval($t->category_id)]);
        $index++;
      }
      Cache::put($key, $data, 1440);
      return Response::json($data);
    }
  }

  public function showBeneficiariesByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));
      $key   = cacheKey('benba', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }
      $beneficiaries = Auth::user()->beneficiaries()->get();
      $records       = array();
      foreach ($beneficiaries as $ben) {
        $ben->name    = Crypt::decrypt($ben->name);
        // find out the expenses for each category:
        $trans_spent  = floatval($ben->transactions()->where('amount', '<', 0)->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
        $trans_earned = floatval($ben->transactions()->where('amount', '>', 0)->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $records[]    = array(
            'category' => $ben->name,
            'spent'    => $trans_spent,
            'earned'   => $trans_earned,
        );
      }
      // everything *outside* of the categories:
      $trans_spent  = floatval($account->transactions()->whereNull('beneficiary_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $trans_earned = floatval($account->transactions()->whereNull('beneficiary_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));


      array_unshift($records, array(
          'category' => 'Outside of beneficiaries',
          'spent'    => $trans_spent,
          'earned'   => $trans_earned,
      ));


      // klopt wie ein busje!
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'ben',
                  'label' => 'Beneficiaries',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'spent',
                  'label' => 'Spent',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'earned',
                  'label' => 'Earned',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($records as $r) {
        if (!($r['spent'] == 0)) {
          $data['rows'][$index]['c'][0]['v'] = $r['category'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['earned'];
          $index++;
        }
      }
      Cache::put($key, $data, 1440);
      return Response::json($data);
    }
  }

}