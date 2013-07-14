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
}