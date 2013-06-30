<?php

class BudgetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function editBudget($id) {
    $budget = Auth::user()->budgets()->find($id);
    if ($budget) {
      $dates = array();
      $start = new DateTime($budget->date);
      $start->sub(new DateInterval('P1Y'));
      for ($i = 0; $i <= 24; $i++) {
        $dates[$start->format('Y-m-d')] = $start->format('F Y');
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
        Cache::flush();
        Session::flash('success', 'The budget has been edited.');
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
      Cache::flush();
      Session::flash('success', 'Budget deleted!');
      return Redirect::to('/home');
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
        $month           = new DateTime($b->date);
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

  public function showBudgetOverview($id) {


    $budget = Auth::user()->budgets()->find($id);

    // avg spent per day must correct for past budgets:
    $periodCorrected = clone Session::get('period');
    $periodCorrected->modify('first day of this month midnight');
    $budgetDate      = new DateTime($budget->date);
    if ($periodCorrected < $budgetDate) {
      // budget is in the future:
      $budget->avgspent = $budget->spent();
    } else if ($periodCorrected == $budgetDate) {
      // budget is THIS month:
      $budget->avgspent = $budget->spent() / intval(Session::get('period')->format('d'));
    } else {
      // budget is in the past:
      $budget->avgspent = $budget->spent() / intval($budgetDate->format('t'));
    }
    $budget->spenttarget = $budget->amount / intval($budgetDate->format('t'));

    // categories & beneficiaries in this budget, transactions:
    $beneficiaries = array();
    $categories    = array();
    foreach ($budget->transactions()->get() as $t) {
      $category    = $t->category()->first();
      $beneficiary = $t->beneficiary()->first();
      // cat
      if (!is_null($category)) {
        if (!isset($categories[$category->id])) {
          $categories[$category->id] = array(
              'name'  => Crypt::decrypt($category->name),
              'id'    => $category->id,
              'spent' => floatval($t->amount) * -1
          );
        } else {
          $categories[$category->id]['spent'] += floatval($t->amount) * -1;
        }
      }
      // ben
      if (!is_null($beneficiary)) {
        if (!isset($beneficiaries[$beneficiary->id])) {
          $beneficiaries[$beneficiary->id] = array(
              'name'  => Crypt::decrypt($beneficiary->name),
              'id'    => $beneficiary->id,
              'spent' => floatval($t->amount) * -1
          );
        } else {
          $beneficiaries[$beneficiary->id]['spent'] += floatval($t->amount) * -1;
        }
      }
    }

    // categories & beneficiaries in this budget, transfers (is expense):
    foreach ($budget->transfers()->where('countasexpense', '=', 1)->get() as $t) {
      $category = $t->category()->first();
      if (!is_null($category)) {
        if (!isset($categories[$category->id])) {
          $categories[$category->id] = array(
              'name'  => Crypt::decrypt($category->name),
              'id'    => $category->id,
              'spent' => floatval($t->amount)
          );
        } else {
          $categories[$category->id]['spent'] += floatval($t->amount);
        }
      }
    }
    if ($budget) {
      return View::make('budgets.overview')->with('budget', $budget)->with('categories', $categories)->with('beneficiaries', $beneficiaries);
    } else {
      return App::abort(404);
    }
  }

  public function newBudget() {


    $budget                 = new Budget;
    $budget->name           = Input::get('name');
    $budget->amount         = floatval(Input::get('amount'));
    $budget->fireflyuser_id = Auth::user()->id;
    $budget->date           = Session::get('period')->format('Y-m-d');
    $validator              = Validator::make($budget->toArray(), Budget::$rules);
    if ($validator->fails()) {
      Log::error('Could not create Budget for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($budget, true));

      return Redirect::to('/home/budget/add')->withErrors($validator)->withInput();
    } else {
      $budget->name = Crypt::encrypt($budget->name);
      $budget->save();
      Cache::flush();
      Session::flash('success', 'The new budget has been created.');
      return Redirect::to('/home');
    }
  }

  public function homeOverviewGraph($id = 0) {

    $key = cacheKey('Budget', 'homeOverviewGraph', $id, Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    // 30 days into the past.
    $end    = clone Session::get('period');
    $end->modify('last day of this month ');
    $today  = new DateTime('now');
    $today->modify('midnight');
    $past   = clone $end;
    $past->modify('first day of this month midnight');
    $budget = Auth::user()->budgets()->find($id);

    $data = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'string',
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
    while ($past <= $end) {
      $data['rows'][$index]['c'][0]['v'] = $past->format('d M');
      if ($past <= $today) {
        $balance                           = $budget->left($past);
        $data['rows'][$index]['c'][1]['v'] = $balance;
        $data['rows'][$index]['c'][2]['v'] = true;
      } else {
        $prediction                        = $budget->predict($past);
        $balance                           = ($balance - $prediction);
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
