<?php

class CategoryController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  /**
   * Same but a longer date range
   * TODO combine and smarter call.
   * @param int $id
   * @return array
   */
  public function overviewGraph($id = 0) {
    $key = cacheKey('Category', 'overviewGraph', $id, Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    $today    = clone Session::get('period');
    $end      = clone($today);
    $past     = self::getFirst();
    $category = Auth::user()->categories()->find($id);

    $data = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'date',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'spent',
                'label' => 'Spent',
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
                'id'    => 'earned',
                'label' => 'Earned',
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
    //$balance = $account->balance($past);
    while ($past <= $end) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';

      $spent                             = floatval($category->transactions()->where('amount', '<', 0)->where('date', '=', $past->format('Y-m-d'))->sum('amount')) * -1;
      $earned                            = floatval($category->transactions()->where('amount', '>', 0)->where('date', '=', $past->format('Y-m-d'))->sum('amount'));
      $certain_spent                     = true;
      $certain_earned                    = true;
      $data['rows'][$index]['c'][1]['v'] = $spent;
      $data['rows'][$index]['c'][2]['v'] = $certain_spent;
      $data['rows'][$index]['c'][3]['v'] = $earned;
      $data['rows'][$index]['c'][4]['v'] = $certain_earned;
      $past->add(new DateInterval('P1D'));
      $index++;
    }

    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  public function showAll() {
    $key = cacheKey('Categories', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data       = array();
      $categories = Auth::user()->categories()->orderBy('id', 'ASC')->get();
      // to get the avg per month we first need the number of months
      $first      = BaseController::getFirst();
      $last       = BaseController::getLast();
      $diff       = $first->diff($last);
      $months     = $diff->m + ($diff->y * 12);

      foreach ($categories as $cat) {
        $cate        = array(
            'id'   => intval($cat->id),
            'name' => Crypt::decrypt($cat->name),
        );
        $trans       = $cat->transactions()->sum('amount');
        $cate['avg'] = $trans / $months;

        $now           = new DateTime('now');
        $thisMonth     = $cat->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $now->format('m-Y'))->sum('amount');
        $cate['month'] = floatval($thisMonth);

        $data[] = $cate;
      }
      Cache::put($key, $data, 1440);
    }
    return View::make('categories.all')->with('categories', $data);
  }

  public function editCategory($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      return View::make('categories.edit')->with('category', $category);
    } else {
      return App::abort(404);
    }
  }

  public function doEditCategory($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $category->name = Input::get('name');
      $validator      = Validator::make($category->toArray(), Category::$rules);
      if ($validator->fails()) {
        Log::error('Could not edit category for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($category, true));
        return Redirect::to('/home/category/edit/' . $category->id)->withErrors($validator)->withInput();
      } else {
        $category->name = Crypt::encrypt($category->name);
        $category->save();
        return Redirect::to('/home/categories');
      }
    } else {
      return App::abort(404);
    }
  }

  public function showOverview($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      return View::make('categories.overview')->with('category', $category);
    } else {
      return App::abort(404);
    }
  }

  public function deleteCategory($id) {

    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $category->delete();
      return Redirect::to('/home/categories');
    } else {
      return App::abort(404);
    }
  }

  public function getCategorySummary($id) {
    $category = Auth::user()->categories()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($category)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));
      $key   = cacheKey('categorysummary', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }
      $startStr = $start->format('jS M Y');
      $endStr   = $end->format('jS M Y');
      // transactions, sum:
      $sum_raw  = floatval($category->transactions()->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));

      $spent_received = $sum_raw > 0 ? 'received' : 'spent';
      $sum_raw        = $sum_raw < 0 ? $sum_raw * -1 : $sum_raw;
      $sum            = mf($sum_raw);
      $diff           = $end->diff($start);
      $avg            = mf($sum_raw / $diff->days);
      $string         = 'In the period between %s and %s (including), you have
          %s %s on "%s". That means you %s %s per day (on average).';
      $formatted      = sprintf($string, $startStr, $endStr, $spent_received, $sum, Crypt::decrypt($category->name), $spent_received, $avg);
      Cache::put($key, $formatted, 1440);
      return Response::json($formatted);
    }
  }

  public function showTransactionsInTimeframe($id) {
    $category = Auth::user()->categories()->find($id);

    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($category)) {
      return App::abort(404);
    } else {

      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $key = cacheKey('transbc', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }

      $trans = $category->transactions()->orderBy('date', 'DESC')->
              where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->
              get();


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
                  'id'    => 'beneficiary',
                  'label' => 'Beneficiary',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'account',
                  'label' => 'Account',
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


        // get cat and save
        if (!is_null($t->beneficiary_id) && !isset($bet[intval($t->beneficiary_id)])) {
          $bet[intval($t->beneficiary_id)] = Crypt::decrypt($t->beneficiary()->first()->name);
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
        $data['rows'][$index]['c'][5]['v'] = (is_null($t->account_id) ? null : $at[intval($t->account_id)]);
        $index++;
      }

      Cache::put($key, $data, 1440);

      return Response::json($data);
    }
  }

  public function showBudgetsInTimeframe($id) {
    $category = Auth::user()->categories()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($category)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $key = cacheKey('budgetsbycategory', $id, $start, $end);
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
        $trans_earned = floatval($budget->transactions()->where('amount', '>', 0)->where('category_id', '=', $category->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $trans_spent  = floatval($budget->transactions()->where('amount', '<', 0)->where('category_id', '=', $category->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;


        $records[] = array(
            'budget' => $budget->name . ' (' . $date->format('F Y') . ')',
            'spent'  => $trans_spent,
            'earned' => $trans_earned,
        );
      }
      // everything *outside* of the budgets:
      $outside_trans_earned = floatval($category->transactions()->where('amount', '>', 0)->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      $outside_trans_spent  = floatval($category->transactions()->where('amount', '<', 0)->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;

      array_unshift($records, array(
          'budget' => 'Outside of budgets',
          'spent'  => $outside_trans_spent,
          'earned' => $outside_trans_earned,
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
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($records as $r) {
        if (!($r['spent'] == 0)) {
          $data['rows'][$index]['c'][0]['v'] = $r['budget'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['earned'];
          $index++;
        }
      }
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }
  public function showBeneficiariesInTimeframe($id) {
    $category = Auth::user()->categories()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($category)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $key = cacheKey('beneficiariesbycategory', $id, $start, $end);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      }

      $start_first = clone $start;
      $end_first   = clone $end;
      $start_first->modify('first day of this month');
      $end_first->modify('first day of this month');


      // all budgets + stuff outside budgets should match this!
      $beneficiaries = Auth::user()->beneficiaries()->get();
      $records = array();
      foreach ($beneficiaries as $beneficiary) {
        $beneficiary->name = Crypt::decrypt($beneficiary->name);
        // find out the expenses for each budget:
        $trans_earned = floatval($beneficiary->transactions()->where('amount', '>', 0)->where('category_id', '=', $category->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $trans_spent  = floatval($beneficiary->transactions()->where('amount', '<', 0)->where('category_id', '=', $category->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;


        $records[] = array(
            'budget' => $beneficiary->name,
            'spent'  => $trans_spent,
            'earned' => $trans_earned,
        );
      }
      // everything *outside* of the budgets:
      $outside_trans_earned = floatval($category->transactions()->where('amount', '>', 0)->whereNull('beneficiary_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      $outside_trans_spent  = floatval($category->transactions()->where('amount', '<', 0)->whereNull('beneficiary_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;

      array_unshift($records, array(
          'budget' => 'Outside of beneficiaries',
          'spent'  => $outside_trans_spent,
          'earned' => $outside_trans_earned,
      ));



      // klopt wie ein busje!
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'cat',
                  'label' => 'Beneficiary',
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
          $data['rows'][$index]['c'][0]['v'] = $r['budget'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['earned'];
          $index++;
        }
      }
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

}