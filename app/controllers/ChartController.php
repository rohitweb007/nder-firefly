<?php

use Carbon\Carbon as Carbon;

class ChartController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function budgetProgress() {
    $this->_debug = Input::get('debug') == 'true' ? true : false;
    $budget       = Input::get('budget');
    if (is_null($budget)) {
      return App::abort(404);
    }
    $key = cacheKey('budgetProgress', $budget, Session::get('period'));

    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    } else {
      // so the current month, right?
      $month = Session::get('period');
      $end   = intval($month->format('t'));
      $clone = new Carbon($month->format('Y-m-d'));
      $clone->modify('first day of this month');

      $data    = array(
          'cols' => array(
              array(
                  'id'    => 'day',
                  'label' => 'Day of the month',
                  'type'  => 'string',
                  'p'     => array('role' => 'domain')
              ),
              array(
                  'id'    => 'left',
                  'label' => $clone->format('F Y'),
                  'type'  => 'number',
                  'p'     => array('role' => 'data')),
              array(
                  'id'    => 'Spentavg',
                  'label' => 'Avg',
                  'type'  => 'number',
                  'p'     => array('role' => 'data')
              ),
          ),
      );
      // get the latest budgets with this name:
      $budgets = Auth::user()->budgets()->orderBy('date', 'DESC')->get();
      $current = null;
      $others  = array();
      foreach ($budgets as $b) {
        $bName = Crypt::decrypt($b->name);
        $bDate = new Carbon($b->date);
        if ($bDate == $clone && $bName == $budget && is_null($current)) {
          $current = $b;
        } else if ($bName == $budget && ((isset($current) && $current->id != $b->id) || is_null($current))) {
          $others[] = $b->id;
        }
      }
      $index           = 0;
      $spent           = 0;
      $previouslySpent = 0;
      for ($i = 1; $i <= $end; $i++) {
        // get expenses for budget on this day.
        $data['rows'][$index]['c'][0]['v'] = $clone->format('j F');
        if (!is_null($current)) {
          $expenses                          = floatval($current->transactions()->where('onetime', '=', 0)->where('date', '=', $clone->format('Y-m-d'))->sum('amount')) * -1;
          // also get 'expenses' from transactions:
          $expenses += floatval($current->transfers()->where('countasexpense', '=', 1)->where('ignoreprediction', '=', 0)->where('date', '=', $clone->format('Y-m-d'))->sum('amount'));
          $spent += $expenses;
          $data['rows'][$index]['c'][1]['v'] = $spent;
        }


        if (count($others) > 0) {
          // now for all previous budgets.
          $oldExpenses                       = (floatval(Auth::user()->transactions()->where('onetime', '=', 0)->whereIn('budget_id', $others)->where(DB::Raw('DATE_FORMAT(`date`,"%e")'), '=', $i)->sum('amount')) * -1) / count($others);
          $oldExpenses += floatval(Auth::user()->transfers()->where('countasexpense', '=', 1)->where('ignoreprediction', '=', 0)->whereIn('budget_id', $others)->where('date', '=', $clone->format('Y-m-d'))->sum('amount'));
          $previouslySpent += $oldExpenses;
          $data['rows'][$index]['c'][2]['v'] = $previouslySpent;
        }
        $clone->addDay();
        $index++;
      }


      if ($this->_debug) {
        return '<pre>' . print_r($data, true) . '</pre>';
      }
      Cache::put($key, $data, 5000);
      return Response::json($data);
    }
  }

  public function predictionChart() {
    // in order to predict the future, we look at the past.
    //$baseAccount = ?;
    //$startBalance = ?;
    $setting      = Auth::user()->settings()->where('name', '=', 'defaultAmount')->first();
    $balance      = intval(Crypt::decrypt($setting->value));
    $account      = Auth::user()->accounts()->orderBy('id', 'ASC')->first();
    $debug        = Input::get('debug') == 'true' ? true : false;
    $this->_debug = $debug;
    $key          = $debug ? cacheKey('prediction', Session::get('period'),rand(1,10000)) : cacheKey('prediction', Session::get('period'));
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
                  'p'     => array('role' => 'data')
              ),
              array(
                  'type' => 'boolean',
                  'p'    => array(
                      'role' => 'certainty'
                  )
              ),
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
      $this->_e('FIRST is ' . $first->format('d M Y'));
      $today     = new Carbon('now');
      $this->_e('Today is ' . $today->format('d M Y'));
      $today->modify('first day of this month');
      $this->_e('Today is ' . $today->format('d M Y'));
      $chartdate = new Carbon('now');
      $chartdate->modify('first day of this month');
      $index     = 0;

      $specificAmount = Auth::user()->settings()->where('name', '=', 'monthlyAmount')->where('date', '=', $today->format('Y-m-d'))->first();
      if ($specificAmount) {
        $balance = intval(Crypt::decrypt($specificAmount->value));
      }

      // loop over each day of the month:
      $this->_e('start month loop');
      for ($i = 1; $i <= intval($today->format('t')); $i++) {
        $this->_e('Now at day #' . $i);
        $current = clone $first;
        $day     = $i - 1;
        if ($day > 0) {
          $current->add(new DateInterval('P' . $day . 'D'));
        }
        $this->_e('Date is now: ' . $current->format('d M Y'));
        // this array will be used to collect average amounts:
        $average      = array();
        // loop over each month:
        // get all transaction results for this day of the month:
        $transactions = Auth::user()->transactions()->where('amount', '<', 0)->where('onetime', '=', 0)
                        ->where(DB::Raw('DATE_FORMAT(`date`,"%e")'), '=', intval($current->format('d')))
                        ->orderBy('amount', 'ASC')->get();
        // lets see what we have

        if (count($transactions) > 0) {
          $min = floatval($transactions[count($transactions) - 1]->amount) * -1;
          $max = floatval($transactions[0]->amount) * -1;

          // fill the array for the averages later on:
          foreach ($transactions as $t) {
            $this->_e('Add to avg['.count($average).'] for transactions: ' . (floatval($t->amount) * -1));
            $average[] = floatval($t->amount) * -1;

          }
        } else {
          $min = 0;
          $max = 0;
        }

        // now do the same for transfers and compare it.
        $transfers = Auth::user()->transfers()->where('countasexpense', '=', 1)->
                        where('ignoreprediction', '=', 0)->orderBy('amount', 'ASC')
                        ->where(DB::Raw('DATE_FORMAT(`date`,"%e")'), '=', intval($current->format('d')))->get();
        if (count($transfers) > 0) {
          $transfer_min = floatval($transfers[count($transfers) - 1]->amount);
          $transfer_max = floatval($transfers[0]->amount);
          $min          = $transfer_min < $min ? $transfer_min : $min;
          $max          = $transfer_max > $max ? $transfer_max : $max;
          unset($transfer_max, $transfer_min);

          // fill the array for the averages later on:
          foreach ($transfers as $t) {
            $this->_e('Add to avg['.count($average).'] for transfers: ' . (floatval($t->amount)));
            $average[] = floatval($t->amount);
          }
        }
        $this->_e('New min: ' . $min);
        $this->_e('New max: ' . $max);

        // calc avg:
        //$avg                               = (($max - $min) / 2) + $min;
        $avg                               = array_sum($average) / count($average);
        $this->_e('New avg: ' . $avg);
        $data['rows'][$index]['c'][0]['v'] = $chartdate->format('j F');
        $data['rows'][$index]['c'][1]['v'] = $account->balance($chartdate); // actual balance
        if ($chartdate > new Carbon('now')) {
          $data['rows'][$index]['c'][2]['v'] = false;
        } else {
          $data['rows'][$index]['c'][2]['v'] = true;
        }
        $data['rows'][$index]['c'][3]['v'] = $balance - $avg; // predicted balance
        $data['rows'][$index]['c'][4]['v'] = $balance - $max; // predicted max expenses.
        $data['rows'][$index]['c'][5]['v'] = $balance - $min; // predicted max expenses.
        $balance                           = $balance - $avg;



        $chartdate->add(new DateInterval('P1D'));
        $index++;
        $this->_e(' ');
      }
      Cache::put($key, $data, 1440);
    }
    if ($debug) {
      return '<pre>' . print_r($data, true) . '</pre>';
    }
    return Response::json($data);
  }

  public function showOverExpendingCategories() {
    $key = cacheKey('overExpendingCategories', Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    $categories = Auth::user()->categories()->get();
    $data       = array(
        'cols' => array(
            array(
                'id'    => 'Category',
                'label' => 'Category',
                'type'  => 'string',
            ), // next spot must be. overspent in pct.
//            array(
//                'id'    => 'toomuch', //
//                'label' => 'Spent more than average',
//                'type'  => 'number',
//            ),
            array(
                'id'    => 'toomuchpct', //
                'label' => 'Overspent pct',
                'type'  => 'number',
            ),
            array(
                'id'    => 'spent',
                'label' => 'Spent so far in total',
                'type'  => 'number',
            ),
            array(
                'id'    => 'judgement',
                'label' => 'Judgement',
                'type'  => 'string',
            ),
            array(
                'id'    => 'spentavg',
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

          $current['c'][0]['v'] = $category->name; // name
          //$current['c'][1]['v'] = $spent - $avg_spent; // over spent
          $current['c'][1]['v'] = $spentpct; // spent so far
          $current['c'][2]['v'] = $spent; // spent so far
          $current['c'][3]['v'] = $descr; // judge
          $current['c'][4]['v'] = $avg_spent; // spent on avg


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
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  private function _e($str) {
    if ($this->_debug) {
      echo $str . '<br>';
    }
  }

}