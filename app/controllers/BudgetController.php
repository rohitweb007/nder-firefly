<?php

use Carbon\Carbon as Carbon;

class BudgetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function editBudget($id) {
    $budget = Auth::user()->budgets()->find($id);
    if ($budget) {
      $dates = array();
      $start = new Carbon($budget->date);
      $start->sub(new DateInterval('P1Y'));
      for ($i = 0; $i <= 24; $i++) {
        $dates[$start->format('Y-m') . '-01'] = $start->format('F Y');
        $start->add(new DateInterval('P1M'));
      }
      return View::make('budgets.edit')->with('budget', $budget)->with('dates', $dates);
    } else {
      return App::abort(404);
    }
  }

  public function doEditBudget($id) {
    $budget = Auth::user()->budgets()->find($id);
    if ($budget) {
      $budget->name   = Input::get('name');
      $budget->amount = floatval(Input::get('amount'));
      $budget->date   = Input::get('date');
      $validator      = Validator::make($budget->toArray(), Budget::$rules);
      if ($validator->fails()) {
        Log::error('Could not edit Budget for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($budget, true));
        return Redirect::to('/home/budget/edit/' . $budget->id)->withErrors($validator)->withInput();
      } else {
        $budget->name = Crypt::encrypt($budget->name);
        $budget->save();
        return Redirect::to('/home/budget/overview/' . $budget->id);
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteBudget($id) {
    $budget = Auth::user()->budgets()->find($id);
    if ($budget) {
      $budget->delete();
      return Redirect::to('/home/budgets');
    } else {
      return Response::error(404);
    }
  }

  public function showAll() {
    $key = cacheKey('Budgets', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data    = array();
      $budgets = Auth::user()->budgets()->orderBy('date', 'DESC')->get();
      foreach ($budgets as $b) {
        $month           = new Carbon($b->date);
        $strMonth        = $month->format('F Y');
        $data[$strMonth] = isset($data[$strMonth]) ? $data[$strMonth] : array();


        $budget = array(
            'name'      => Crypt::decrypt($b->name),
            'amount'    => floatval($b->amount),
            'spent'     => $b->spent(),
            'overspent' => false,
            'id'        => intval($b->id),
            'left'      => 0
        );

        if ($budget['amount'] != 0) {
          $pct            = ($budget['spent'] / $budget['amount']) * 100;
          $budget['left'] = $budget['amount'] - $budget['spent'];
          if ($pct > 100) {
            $budget['overspent'] = true;
            $budget['pct']       = round(($budget['amount'] / $budget['spent']) * 100, 0);
          } else {

            $budget['pct'] = round($pct);
          }
        }


        $data[$strMonth][] = $budget;
      }
    }
    return View::make('budgets.all')->with('budgets', $data);
  }

  public function addBudget() {
    return View::make('budgets.add');
  }


  public function newBudget() {


    $budget                 = new Budget;
    $budget->name           = Input::get('name');
    $budget->amount         = floatval(Input::get('amount'));
    $budget->fireflyuser_id = Auth::user()->id;
    $budget->date           = Session::get('period')->format('Y-m') . '-01';
    $validator              = Validator::make($budget->toArray(), Budget::$rules);
    if ($validator->fails()) {
      Log::error('Could not create Budget for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($budget, true));

      return Redirect::to('/home/budget/add')->withErrors($validator)->withInput();
    } else {
      $budget->name = Crypt::encrypt($budget->name);
      $budget->save();
      return Redirect::to('/home');
    }
  }

  public function homeOverviewChart($id = 0) {

    $key = cacheKey('Budget', 'homeOverviewChart', $id, Session::get('period'));

    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }

    // 30 days into the past.
    $end   = clone Session::get('period');
    $end->modify('last day of this month ');
    $today = new Carbon('now');
    $today->modify('midnight');
    $past  = clone $end;
    $past->modify('first day of this month midnight');

    $budget = Auth::user()->budgets()->find($id);


    $data = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'date',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'balance',
                'label' => 'Left',
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

    $index   = 0;
    $balance = $budget->amount;

    // get the prediction points (if any):
    $points     = $budget->budgetpredictionpoints()->get();
    $prediction = array();
    foreach ($points as $p) {
      $prediction[intval($p->day)] = floatval($p->amount);
    }

    while ($past <= $end) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
      if ($past <= $today) {
        $balance                           = $budget->left($past);
        $data['rows'][$index]['c'][1]['v'] = $balance;
        $data['rows'][$index]['c'][2]['v'] = true;
      } else {
        $balance                           = ($balance - (isset($prediction[$day]) ? $prediction[$day] : 0 ) );
        $data['rows'][$index]['c'][1]['v'] = $balance;
        $data['rows'][$index]['c'][2]['v'] = false;
      }

      $past->add(new DateInterval('P1D'));
      $index++;
    }

    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

}
