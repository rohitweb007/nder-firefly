<?php

use Carbon\Carbon as Carbon;

class OverviewController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showOverview($object, $id) {
    $start = !is_null(Input::get('start')) ? new Carbon(Input::get('start')) : null;
    $end   = !is_null(Input::get('end')) ? new Carbon(Input::get('end')) : null;

    $objects = Str::plural($object);
    $db      = Auth::user()->$objects()->find($id);
    if ($db) {
      return View::make('overview.overview')->with('object', $db)->with('name', $object)->with('names', $objects)
                      ->with('start', $start)->with('end', $end);
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
    $key = cacheKey($object, 'overviewChart', $id, Session::get('period'));
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
          $transactions                      = floatval($db->transactions()->where('onetime', '=', 0)->where('date', '=', $past->format('Y-m-d'))->sum('amount'));
          $transfers                         = floatval($db->transfers()->where('countasexpense', '=', 1)->where('date', '=', $past->format('Y-m-d'))->sum('amount'));
          $sum                               = $transactions + $transfers;
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
      $past->addDay();
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

      if ($object != 'beneficiary' && $chart != 'account' && $chart != 'beneficiary') {
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

    $transactions = floatval(Auth::user()->transactions()->whereNull($chart . '_id')->where('amount', $operator, 0)->where($object . '_id', '=', $db->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
    if ($chart != 'beneficiary' && $chart != 'account' && $object != 'beneficiary') {
      // transfers hebben geen accountid, die hebben account_from en account_to

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

  function showTransactions($object) {
    $id       = intval(Input::get('id'));
    $start    = new Carbon(Input::get('start'));
    $end      = new Carbon(Input::get('end'));
    $objects  = Str::plural($object);
    // we might filter on expenses or incomes:
    $modifier = Input::get('modifier');

    // we might need to filter on a certain object.
    // this object is called the child.
    // so the object might be an account, and the child might be beneficiary
    $children = Input::get('childType'); // yes this is confusing.
    $child    = !is_null($children) ? Str::singular($children) : null;



    // find the ID that this child signifies.
    // this might be a beneficiary name or a budget name
    $selection    = Input::get('childValue');
    $selectedItem = false; // false means no selection made.
    if (!strstr($selection, '(no ') === false) {
      $selectedItem = null; // null means select where NULL.
    }

    if (!is_null($selection) && $selectedItem === false) {
      // find it:
      switch ($child) {
        default:
          // just find it.
          $items = Auth::user()->$children()->get();
          foreach ($items as $item) {
            if (Crypt::decrypt($item->name) == $selection) {
              $selectedItem = $item;
            }
          }
          break;
        case 'budget':
          // keep the date in mind!
          $split       = explode('(', $selection);
          $budget_name = trim($split[0]);
          $dateStr     = trim(str_replace(')', '', $split[1]));
          $date        = new Carbon($dateStr);
          $budgets     = Auth::user()->budgets()->where('date', '=', $date->format('Y-m-d'))->get();
          foreach ($budgets as $b) {
            if (Crypt::decrypt($b->name) == $budget_name) {
              $selectedItem = $b;
            }
          }
          break;
      }
    }
    //var_dump($selectedItem);exit;


    $db = Auth::user()->$objects()->find($id);

    // create (and find)  a cache key:
    $key = cacheKey('PieChart', $id, $start, $end, $objects,$children,$modifier,$selection);
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    if (!$db) {
      return App::abort(404);
    }

    // find the transactions and transfers for $object in range.
    $index             = 0;
    $data              = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'date',
            ),
            array(
                'id'    => 'description',
                'label' => 'Description',
                'type'  => 'string',
            ),
            array(
                'id'    => 'amount',
                'label' => 'Amount',
                'type'  => 'number',
            ),
            array(
                'id'    => 'account',
                'label' => 'Account(s)',
                'type'  => 'string',
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
            array(
                'id'    => 'target',
                'label' => 'Target',
                'type'  => 'string',
            ),
        ),
        'rows' => array()
    );
    $transactionsQuery = $db->transactions()->
            leftJoin('accounts', 'accounts.id', '=', 'account_id')->
            leftJoin('budgets', 'budgets.id', '=', 'budget_id')->
            leftJoin('categories', 'categories.id', '=', 'category_id')->
            leftJoin('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_id')->
            orderBy('transactions.date', 'DESC')->
            where('transactions.date', '>=', $start->format('Y-m-d'))->
            where('transactions.date', '<=', $end->format('Y-m-d'));
    if ($modifier == 'income') {
      $transactionsQuery->where('transactions.amount', '>', 0);
    } else if ($modifier == 'expenses') {
      $transactionsQuery->where('transactions.amount', '<', 0);
    }
    if (is_null($selectedItem)) {
      $transactionsQuery->whereNull($child . '_id');
    } else if (!is_null($selectedItem) && !$selectedItem === false) {
      $transactionsQuery->where($child . '_id', '=', $selectedItem->id);
    }



    $transactions = $transactionsQuery->get(array(
        'transactions.id',
        'accounts.name AS account_name',
        'budgets.name AS budget_name',
        'categories.name AS category_name',
        'beneficiaries.name AS beneficiary_name',
        'transactions.date', 'description', 'transactions.amount', 'onetime'
    ));
    foreach ($transactions as $t) {
      $date = new Carbon($t->date);

      $data['rows'][$index]['c'][0]['v'] = 'Date(' . intval($date->format('Y')) . ', ' . (intval($date->format('n')) - 1) . ', ' . intval($date->format('j')) . ')';
      $data['rows'][$index]['c'][1]['v'] = Crypt::decrypt($t->description);
      $data['rows'][$index]['c'][2]['v'] = floatval($t->amount);
      $data['rows'][$index]['c'][3]['v'] = is_null($t->account_name) ? null : Crypt::decrypt($t->account_name);
      $data['rows'][$index]['c'][4]['v'] = is_null($t->budget_name) ? null : Crypt::decrypt($t->budget_name);
      $data['rows'][$index]['c'][5]['v'] = is_null($t->category_name) ? null : Crypt::decrypt($t->category_name);
      $data['rows'][$index]['c'][6]['v'] = is_null($t->beneficiary_name) ? null : Crypt::decrypt($t->beneficiary_name);
      $data['rows'][$index]['c'][7]['v'] = null;
      $index++;
    }

    // get the transfers (no filter yet if applicable)
    $searchTransfers = null;
    if ($object != 'account' && $object != 'beneficiary') {
      $searchTransfers = 'transfers';
    } else if ($object == 'account') {

      if($modifier == 'income') {
        $searchTransfers = 'transfersto';
      } else if ($modifier == 'expenses') {
        $searchTransfers = 'transfersfrom';
      }
    }

    if(!is_null($searchTransfers)) {

      $transfersQuery = $db->$searchTransfers()->
                      leftJoin('categories', 'categories.id', '=', 'category_id')->
                      leftJoin('accounts as af', 'af.id', '=', 'account_from')->
                      leftJoin('accounts as at', 'at.id', '=', 'account_to')->
                      leftJoin('budgets', 'budgets.id', '=', 'budget_id')->
                      leftJoin('targets', 'targets.id', '=', 'target_id')->
                      where('countasexpense', '=', 1)->
                      where('transfers.date', '>=', $start->format('Y-m-d'))->
                      where('transfers.date', '<=', $end->format('Y-m-d'))->
                      orderBy('transfers.date', 'DESC')->orderBy('transfers.created_at', 'DESC');


      if (is_null($selectedItem) && $child != 'beneficiary') {
        $transfersQuery->whereNull($child . '_id');
      } else if (!is_null($selectedItem) && !$selectedItem === false && $child != 'beneficiary') {
        $transfersQuery->where($child . '_id', '=', $selectedItem->id);
      }

      $transfers = $transfersQuery->get(
              array(
                  'transfers.id',
                  'categories.name AS category_name',
                  'at.name AS account_to_name',
                  'af.name AS account_from_name',
                  'budgets.name AS budget_name',
                  'targets.description AS target_description',
                  'transfers.date', 'transfers.description', 'transfers.amount', 'countasexpense', 'ignoreprediction'
              )
      );

      foreach ($transfers as $t) {
        $date = new Carbon($t->date);

        $data['rows'][$index]['c'][0]['v'] = 'Date(' . intval($date->format('Y')) . ', ' . (intval($date->format('n')) - 1) . ', ' . intval($date->format('j')) . ')';
        $data['rows'][$index]['c'][1]['v'] = Crypt::decrypt($t->description);
        $data['rows'][$index]['c'][2]['v'] = floatval($t->amount);
        $data['rows'][$index]['c'][3]['v'] = !is_null($t->account_from_name) && !is_null($t->account_to_name) ? Crypt::decrypt($t->account_from_name) . ' &rarr;  ' . Crypt::decrypt($t->account_to_name) : null;
        $data['rows'][$index]['c'][4]['v'] = is_null($t->budget_name) ? null : Crypt::decrypt($t->budget_name);
        $data['rows'][$index]['c'][5]['v'] = is_null($t->category_name) ? null : Crypt::decrypt($t->category_name);
        $data['rows'][$index]['c'][6]['v'] = is_null($t->beneficiary_name) ? null : Crypt::decrypt($t->beneficiary_name);
        $data['rows'][$index]['c'][7]['v'] = null;
        $index++;
      }
    }
    Cache::put($key,$data,1440);
    return Response::json($data);
  }

}