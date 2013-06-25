<?php

class BudgetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
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
    $categories = array();
    foreach ($budget->transactions()->get() as $t) {
      $category = $t->category()->first();
      $beneficiary = $t->beneficiary()->first();
      // cat
      if (!is_null($category)) {
        if (!isset($categories[$category->id])) {
          $categories[$category->id] = array(
              'name' => Crypt::decrypt($category->name),
              'id' => $category->id,
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
              'name' => Crypt::decrypt($beneficiary->name),
              'id' => $beneficiary->id,
              'spent' => floatval($t->amount) * -1
          );
        } else {
          $beneficiaries[$beneficiary->id]['spent'] += floatval($t->amount) * -1;
        }
      }
    }

    // categories & beneficiaries in this budget, transfers (is expense):
    foreach ($budget->transfers()->where('countasexpense','=',1)->get() as $t) {
      $category = $t->category()->first();
      if (!is_null($category)) {
        if (!isset($categories[$category->id])) {
          $categories[$category->id] = array(
              'name' => Crypt::decrypt($category->name),
              'id' => $category->id,
              'spent' => floatval($t->amount)
          );
        } else {
          $categories[$category->id]['spent'] += floatval($t->amount);
        }
      }
    }
    if ($budget) {
      return View::make('budgets.show')->with('budget', $budget)->with('categories',$categories)->with('beneficiaries',$beneficiaries);
    } else {
      return Response::error(404);
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
      return Redirect::to('/home/budget/add')->withErrors($validator)->withInput();
    } else {
      $budget->name = Crypt::encrypt($budget->name);
      $budget->save();
      Session::flash('success', 'The new budget has been created.');
      return Redirect::to('/home');
    }
  }

  public function homeOverviewGraph($id = 0) {
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

    $index = 0;
    $balance = $budget->amount;
    while ($past <= $end) {
      $data['rows'][$index]['c'][0]['v'] = $past->format('d M');
      if ($past <= $today) {
        $balance = $budget->left($past);
        $data['rows'][$index]['c'][1]['v'] = $balance;
        $data['rows'][$index]['c'][2]['v'] = true;
      } else {
        $prediction = $budget->predict($past);
        $balance = ($balance - $prediction);
        $data['rows'][$index]['c'][1]['v'] = $balance;
        $data['rows'][$index]['c'][2]['v'] = false;
      }

      $past->add(new DateInterval('P1D'));
      $index++;
    }
    return Response::json($data);
  }

}