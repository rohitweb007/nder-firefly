<?php

use Carbon\Carbon as Carbon;

class OverviewController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showOverview($object, $id) {
    $objects = Str::plural($object);
    $db      = Auth::user()->$objects()->find($id);
    if ($db) {
      return View::make('overview.overview')->with('object', $db)->with('name', $object)->with('names', $objects);
    } else {
      return App::abort(404);
    }
  }

  /**
   * Same but a longer date range
   * TODO combine and smarter call.
   * @param type $id
   * @return type
   */
  public function showOverviewChart($object, $id = 0) {
    $key = cacheKey($object, 'overviewGraph', $id, Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    $objects = Str::plural($object);
    $today   = clone Session::get('period');
    $end     = clone($today);
    $end->modify('last day of this month');
    $past    = self::getFirst();
    $db      = Auth::user()->$objects()->find($id);

    if ($object == 'budget') {
      $past = new Carbon($db->date);
      $past->subDay();
    }
    $data = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'date',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'amount',
                'label' => 'Amount',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
            array(
                'type' => 'boolean',
                'p'    => array(
                    'role' => 'certainty'
                )
            ),
        ),
        'rows' => array()
    );

    $index = 0;
    // exceptions?
    switch ($object) {
      default:
        break;
      case 'account':
        $amount     = $db->balance($past);
        break;
      case 'budget':
        $balance    = $db->amount;
        // get the prediction points (if any):
        $points     = $db->budgetpredictionpoints()->get();
        $prediction = array();
        foreach ($points as $p) {
          $prediction[intval($p->day)] = floatval($p->amount);
        }
        break;
    }
    while ($past <= $end) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';

      // exceptions?
      switch ($object) {
        default:
          // let's actually do the defaults:
          $transactions = floatval($db->transactions()->where('onetime','=',0)->where('date','=',$past->format('Y-m-d'))->sum('amount'));
          $transfers = floatval($db->transfers()->where('countasexpense','=',1)->where('date','=',$past->format('Y-m-d'))->sum('amount'));
          $sum = $transactions + $transfers;
          $data['rows'][$index]['c'][1]['v'] = $sum;
          $data['rows'][$index]['c'][2]['v'] = true;
          break;
        case 'budget':
          if ($past <= $today) {
            $balance                           = $db->left($past);
            $data['rows'][$index]['c'][1]['v'] = $balance;
            $data['rows'][$index]['c'][2]['v'] = true;
          } else {
            $balance                           = ($balance - (isset($prediction[$day]) ? $prediction[$day] : 0 ) );
            $data['rows'][$index]['c'][1]['v'] = $balance;
            $data['rows'][$index]['c'][2]['v'] = false;
          }
          break;
        case 'beneficiary':
          $transactions                      = floatval(Auth::user()->transactions()->where('beneficiary_id', '=', $db->id)->where('date', '=', $past->format('Y-m-d'))->sum('amount'));
          $data['rows'][$index]['c'][1]['v'] = $transactions;
          $data['rows'][$index]['c'][2]['v'] = true;
          break;

        case 'account':
          if ($past > $today) {
            $prediction = $db->predict($past);
            $balance    = $balance - $prediction;
            $certain    = false;
          } else {
            $balance = $db->balance($past);
            $certain = true;
          }
          $data['rows'][$index]['c'][1]['v'] = $balance;
          $data['rows'][$index]['c'][2]['v'] = $certain;
          break;
      }
      $past->add(new DateInterval('P1D'));
      $index++;
    }

    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  public function showPieChart($object) {
    // first get parameters:
    $id    = intval(Input::get('id'));
    $start = new Carbon(Input::get('start'));
    $end   = new Carbon(Input::get('end'));
    $type  = Input::get('type');

    $charts = Input::get('chart'); // hier zoeken we naar
    $chart  = Str::singular($charts);

    $objects = Str::plural($object); // in dit object.

    $db = Auth::user()->$objects()->find($id);

    // create (and find)  a cache key:
    $key = cacheKey('PieChart', $id, $start, $end, $type, $objects, $charts);
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }

    // create chart data:
    $data = array(
        'cols' => array(
            array(
                'id'    => $object,
                'label' => ucfirst($object),
                'type'  => 'string',
            ),
            array(
                'id'    => 'amount',
                'label' => 'Amount',
                'type'  => 'number',
            ),
        ),
        'rows' => array()
    );

    // no object? fail
    if (!$db) {
      return App::abort(404);
    }
    // object is chart?
    if ($chart == $object) {
      return Response::json($data);
    }

    // wrong type? fail
    if (!in_array($type, array('income', 'expenses'))) {
      return App::abort(500);
    }

    $start_first = clone $start;
    $end_first   = clone $end;
    $start_first->modify('first day of this month');
    $end_first->modify('first day of this month');
    // all objects + stuff outside objects should match this!
    $dbObjects   = Auth::user()->$charts()->orderBy('created_at', 'DESC');


    // special case for budget:
    if ($chart == 'budget') {
      $dbObjects->where('date', '>=', $start_first->format('Y-m-d'))->where('date', '<=', $end_first->format('Y-m-d'));
    }
    $result = $dbObjects->get();


    $records  = array();
    $operator = $type == 'income' ? '>' : '<';
    if ($object == 'account') {
      $acc = $type == 'income' ? 'account_to' : 'account_from';
    } else {
      $acc = $object . '_id';
    }

    foreach ($result as $dbObject) {

      $name = Crypt::decrypt($dbObject->name);

      if ($chart == 'budget') {
        $date = new Carbon($dbObject->date);
        $name = Crypt::decrypt($dbObject->name) . ' (' . strtolower($date->format('F Y')) . ')';
      }



      // transactions and transfers
      $transactions = floatval($dbObject->transactions()->where('amount', $operator, 0)->where($object . '_id', '=', $db->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      // exception for beneficiary



      if ($chart != 'beneficiary') {

        $transfers = floatval($dbObject->transfers()->where($acc, '=', $db->id)->where('countasexpense', '=', 1)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      } else {
        $transfers = 0;
      }
      $sum       = ($transactions + $transfers) < 0 ? ($transactions + $transfers) * -1 : ($transactions + $transfers);
      $array     = array(
          'name'   => $name,
          'amount' => $sum
      );
      $records[] = $array;
    }
    // add expenses outside objects (for good measure).

    $transactions = floatval(Auth::user()->transactions()->whereNull($chart . '_id')->where('amount', $operator, 0)->where($object.'_id', '=', $db->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
    if ($chart != 'beneficiary') {
      $transfers = floatval(Auth::user()->transfers()->whereNull($chart . '_id')->where($acc, '=', $db->id)->where('countasexpense', '=', 1)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
    } else {
      $transfers = 0;
    }
    $sum       = ($transactions + $transfers) < 0 ? ($transactions + $transfers) * -1 : ($transactions + $transfers);
    $records[] = array('name'   => '(no ' . $chart . ')', 'amount' => $sum);


    $i = 0;
    foreach ($records as $index => $record) {
      if ($record['amount'] != 0) {
        $data['rows'][$i]['c'][0]['v'] = $record['name'];
        $data['rows'][$i]['c'][1]['v'] = $record['amount'];
        $i++;
      }
    }
    Cache::put($key, $data, 1440);

    return Response::json($data);
  }

}