<?php
use Carbon\Carbon as Carbon;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComparisionController
 *
 * @author sander
 */
class ComparisionController extends BaseController {

  private $_base;
  private $_compares = array();
  private $_today;

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  private function _getValues() {
    $this->_today = new Carbon('now');
    $this->_today->modify('midnight');
    if (
            is_null(Input::get('base')) ||
            is_null(Input::get('compare')) ||
            (!is_null(Input::get('compare')) && !is_array(Input::get('compare')) )
    ) {
      return App::abort(500);
    }

    $validator = Validator::make(array('date' => Input::get('base')), array('date' => 'required|before:2038-01-01|after:1990-01-01'));
    if ($validator->fails()) {
      return App::abort(500);
    } else {
      // save base
      $this->_base = new Carbon(Input::get('base'));
      // if the base is in the current month, we need to edit it
      // to reflect that.
      if ($this->_base->format('m-Y') == $this->_today->format('m-Y')) {
        $this->_base = clone $this->_today;
      } else {
        $this->_base->modify('last day of this month');
      }
      // validate each compare
      foreach (Input::get('compare') as $x) {
        $validator = Validator::make(array('base' => Input::get('base'), 'date' => $x), array('date' => 'required|different:base|before:2038-01-01|after:1990-01-01'));
        if ($validator->fails()) {
          return App::abort(500);
        } else {
          $this->_compares[] = new Carbon($x);
        }
      }
    }


    //$base = Input::get('base');
  }

  public function basicTable() {
    $key = cacheKey('basicTable', md5(json_encode(Input::all())));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    $this->_getValues();
    $monthName = count($this->_compares) == 1 ? $this->_compares[0]->format('F Y') : 'for selection';

    $data = array(
        'cols' => array(
            array(
                'id'    => 'descr',
                'label' => 'Value',
                'type'  => 'string',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'spent',
                'label' => 'Spent',
                'type'  => 'number',
                'p'     => array('role' => 'data')),
            array(
                'id'    => 'earned',
                'label' => 'Earned',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
            array(
                'id'    => 'diff',
                'label' => 'Difference',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
        ),
        'rows' => array()
    );



    $spent  = floatval(Auth::user()->transactions()->where('amount', '<', 0)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount')) * -1;
    $earned = floatval(Auth::user()->transactions()->where('amount', '>', 0)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount'));

    $data['rows'][0]['c'][0]['v'] = $this->_base->format('F Y');
    $data['rows'][0]['c'][1]['v'] = $spent;
    $data['rows'][0]['c'][2]['v'] = $earned;
    $data['rows'][0]['c'][3]['v'] = ($earned - $spent);


    $compares = array();
    foreach (Input::get('compare') as $c) {
      $d          = new Carbon($c);
      $compares[] = $d->format('m-Y');
    }

    $prevSpent  = (floatval(Auth::user()->transactions()->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->where('amount', '<', 0)->sum('amount')) / count($compares)) * -1;
    $prevEarned = floatval(Auth::user()->transactions()->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->where('amount', '>', 0)->sum('amount')) / count($compares);

    $index = 1;
    if ($this->_base == $this->_today) {
      // nog eens, maar dan "tot nu toe".
      $sofarSpent  = (floatval(Auth::user()->transactions()->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->where('amount', '<', 0)->sum('amount')) / count($compares)) * -1;
      $sofarEarned = (floatval(Auth::user()->transactions()->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->where('amount', '>', 0)->sum('amount')) / count($compares));

      $data['rows'][$index]['c'][0]['v'] = 'Comparable ' . $monthName;
      $data['rows'][$index]['c'][1]['v'] = $sofarSpent;
      $data['rows'][$index]['c'][2]['v'] = $sofarEarned;
      $data['rows'][$index]['c'][3]['v'] = ($sofarEarned - $sofarSpent);
      $index++;
    }
    unset($compares);



    $data['rows'][$index]['c'][0]['v'] = 'In total ' . $monthName;
    $data['rows'][$index]['c'][1]['v'] = $prevSpent;
    $data['rows'][$index]['c'][2]['v'] = $prevEarned;
    $data['rows'][$index]['c'][3]['v'] = ($prevEarned - $prevSpent);
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  public function basicChart() {
    $this->_getValues();
    $key = cacheKey('basicChart', md5(json_encode(Input::all())));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    // set some vars:
    $today    = new Carbon('now');
    $account  = intval(Input::get('account')) > 0 ? Auth::user()->accounts()->find(intval(Input::get('account'))) : null;
    $accounts = is_null($account) ? Auth::user()->accounts()->get() : null;
    // prep the data:
    $data     = array(
        'cols' => array(
            array(
                'id'    => 'day',
                'label' => 'Day',
                'type'  => 'date',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'base',
                'label' => 'Base balance',
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
                'id'    => 'compare',
                'label' => 'Compare balance',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
        ),
        'rows' => array()
    );

    // update column names:
    if ($account) {
      $data['cols'][1]['label'] = $this->_base->format('F Y') . ' on ' . Crypt::decrypt($account->name);
      if (count($this->_compares) == 1) {
        $data['cols'][3]['label'] = $this->_compares[0]->format('F Y') . ' on ' . Crypt::decrypt($account->name);
      } else {
        $data['cols'][3]['label'] = 'Selected months on ' . Crypt::decrypt($account->name);
      }
    } else {
      $data['cols'][1]['label'] = $this->_base->format('F Y') . ' on all accounts';
      if (count($this->_compares) == 1) {
        $data['cols'][3]['label'] = $this->_compares[0]->format('F Y') . ' on all accounts';
      } else {
        $data['cols'][3]['label'] = 'Selected months on all accounts';
      }
    }

    // we work on the basis of 31 days.
    $index    = 0;
    $compares = Input::get('compare');
    for ($day = 1; $day <= 31; $day++) {
      $currentBaseDate                   = new Carbon($this->_base->format('Y-m-') . $day);
      // first the base information:
      $month                             = intval($currentBaseDate->format('n')) - 1;
      $year                              = intval($currentBaseDate->format('Y'));
      $day                               = intval($currentBaseDate->format('j'));
      $data['rows'][$index]['c'][0]['v'] ='Date(' . $year . ', ' . $month . ', ' . $day . ')';

      // if accountID, work on balance for this month:
      if (!is_null($account)) {
        $data['rows'][$index]['c'][1]['v'] = $account->balance($currentBaseDate);
      } else if (!is_null($accounts)) {
        // loop over accounts, sum up balance.
        $sum = 0;
        foreach ($accounts as $a) {
          $sum += $a->balance($currentBaseDate);
        }
        $data['rows'][$index]['c'][1]['v'] = $sum;
      }
      $data['rows'][$index]['c'][2]['v'] = $currentBaseDate >= $today ? false : true;

      // now work on the "past".
      $comparesums = array();
      foreach ($compares as $currentCompare) {
        // hier komen sowieso sommetjes uit voor elke dag.
        // zo optellen en avg.
        $compareBase        = new Carbon($currentCompare);
        $currentCompareDate = new Carbon($compareBase->format('Y-m-') . $day);
        if (!is_null($account)) {
          // get for just the one account, add to comparesums and done.
          $comparesums[] = $account->balance($currentCompareDate);
        } else if (!is_null($accounts)) {
          $sum = 0;
          // get for all accounts, add to comparesums and done.
          foreach ($accounts as $a) {
            $sum += $a->balance($currentCompareDate);
          }
          $comparesums[] = $sum;
        }
      }
      $data['rows'][$index]['c'][3]['v'] = count($comparesums) > 0 ? array_sum($comparesums) / count($comparesums) : array_sum($comparesums);

      $index++;
    }
    Cache::put($key, $data, 1440);

    return Response::json($data);
  }

  public function compareBudgets() {
    $this->_getValues();
    $key = cacheKey('budgets', md5(json_encode(Input::all())));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }

    $data = array(
        'cols' => array(
            array(
                'id'    => 'cat',
                'label' => 'Budget',
                'type'  => 'string',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'base',
                'label' => $this->_base->format('F Y'),
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
            array(
                'id'    => 'compare',
                'label' => 'Selection',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
        ),
        'rows' => array()
    );
    if (count($this->_compares) == 1) {
      $data['cols'][2]['label'] = $this->_compares[0]->format('F Y');
    }

    $compares = array();
    foreach (Input::get('compare') as $c) {
      $d          = new Carbon($c);
      $compares[] = $d->format('m-Y');
    }

    // new format for compares
    $longCompares = array();
    foreach (Input::get('compare') as $c) {
      $d              = new Carbon($c);
      $longCompares[] = $d->format('Y-m-d');
    }
    // expenses for this month without any budget:
    $nobud_transactions = floatval(Auth::user()->transactions()->where('amount', '<', 0)->whereNull('budget_id')->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount')) * -1;
    $nobud_transfers    = floatval(Auth::user()->transfers()->where('countasexpense', '=', 1)->whereNull('budget_id')->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount'));

    // expenses for prev without budget
    $nobud_prev_transactions = (floatval(Auth::user()->transactions()->where('amount', '<', 0)->whereNull('budget_id')->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->sum('amount')) / count($compares)) * -1;
    $nobud_prev_transfers    = (floatval(Auth::user()->transfers()->where('countasexpense', '=', 1)->whereNull('budget_id')->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->sum('amount')) / count($compares));


    $data['rows'][0]['c'][0]['v'] = '(no budget)';
    $data['rows'][0]['c'][1]['v'] = ($nobud_transactions + $nobud_transfers);
    $data['rows'][0]['c'][2]['v'] = $nobud_prev_transactions + $nobud_prev_transfers;

    // get this months budgets. Save the names.
    $budgets   = Auth::user()->budgets()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->get();
    $index     = 1;
    $relations = array();
    foreach ($budgets as $budget) {
      $name = Crypt::decrypt($budget->name);


      // expenses in this budget:
      $transactions = floatval($budget->transactions()->where('amount', '<', 0)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount')) * -1;
      $transfers    = floatval($budget->transfers()->where('countasexpense', '=', 1)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount'));
      $sum          = $transactions + $transfers;
      if ($sum > 0) {
        $data['rows'][$index]['c'][0]['v'] = $name;
        $data['rows'][$index]['c'][1]['v'] = $sum;
        $data['rows'][$index]['c'][2]['v'] = NULL;
        $relations[$index]                 = $name;
        $index++;
      }
      // past is NULL for now.
    }


    $allBudgets = Auth::user()->budgets()->whereIn('date', $longCompares)->get();
    //var_dump($compares);
    $oldBudgets = array();
    foreach ($allBudgets as $budget) {
      $oldBudgets[intval($budget->id)] = Crypt::decrypt($budget->name);
    }
    //var_dump($oldBudgets);




    foreach ($relations as $index => $name) {
      // try to find a past budget with this name and this date.
      $oldids = array();
      foreach ($oldBudgets as $oldID => $oldBudget) {
        if ($oldBudget == $name) {
          $oldids[] = $oldID;
        }
      }
      // now get the expenses for this past budget (group);
      if (count($oldids) > 0) {
        //echo 'For index ' . $index . '  and budget ' . $name . ' we look for '.join($oldids,', ').' <br />';
        $prev_transactions                 = (floatval(Auth::user()->transactions()
                                ->whereIn('budget_id', $oldids)
                                ->where('amount', '<', 0)
                                ->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))
                                ->sum('amount')
                ) * -1) / count($oldids);
        $prev_transfers                    = floatval(Auth::user()->transfers()
                                ->whereIn('budget_id', $oldids)
                                ->where('countasexpense', '=', 1)
                                ->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))
                                ->sum('amount')) / count($oldids);
        $data['rows'][$index]['c'][2]['v'] = ($prev_transactions + $prev_transfers);
        //echo 'Sum is '.$data['rows'][$index]['c'][2]['v'].' <br>';
      }
    }



    return Response::json($data);
  }

  public function compareCategories() {
    // three columns: category name, this month and the previous month.
    // can we force HTML in this table by adding it to the data?
    $this->_getValues();
    $key = cacheKey('categories', md5(json_encode(Input::all())));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    $compares = array();
    foreach (Input::get('compare') as $c) {
      $d          = new Carbon($c);
      $compares[] = $d->format('m-Y');
    }


    $data = array(
        'cols' => array(
            array(
                'id'    => 'cat',
                'label' => 'Category',
                'type'  => 'string',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'base',
                'label' => $this->_base->format('F Y'),
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
            array(
                'id'    => 'compare',
                'label' => 'Selection',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
        ),
        'rows' => array()
    );
    if (count($this->_compares) == 1) {
      $data['cols'][2]['label'] = $this->_compares[0]->format('F Y');
    }

    // first a special row:
    // expenses without category.
    // needs to be said as well.
    $nocat_transactions = floatval(Auth::user()->transactions()->whereNull('category_id')->
                            where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount')) * -1;
    $nocat_transfers    = floatval(Auth::user()->transfers()->where('countasexpense', '=', 1)->whereNull('category_id')->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount'));



    $nocat_prev_transactions = (floatval(Auth::user()->transactions()->whereNull('category_id')->
                            where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->sum('amount')) / count($compares)) * -1;

    $nocat_prev_transfers = (floatval(Auth::user()->transfers()->where('countasexpense', '=', 1)->whereNull('category_id')->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->sum('amount')) / count($compares));

    $data['rows'][0]['c'][0]['v'] = '(no category)';
    $data['rows'][0]['c'][1]['v'] = ($nocat_transactions + $nocat_transfers);
    $data['rows'][0]['c'][2]['v'] = $nocat_prev_transactions + $nocat_prev_transfers;

    // for the 'past', we can look at $_base for the day?
    // go for current month:
    $categories = Auth::user()->categories()->get();
    $index      = 1;
    foreach ($categories as $category) {
      // current month's expenses in this category:
      $transactions = floatval($category->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount')) * -1;
      $transfers    = floatval($category->transfers()->where('countasexpense', '=', 1)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $this->_base->format('m-Y'))->sum('amount'));
      $sum          = $transactions + $transfers;

      if ($sum > 0) {
        $prev_transactions = floatval($category->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', intval($this->_base->format('d')))->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->sum('amount')) * -1;
        $prev_transfers    = floatval($category->transfers()->where('countasexpense', '=', 1)->where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', DB::Raw(intval($this->_base->format('d'))))->whereIn(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), $compares)->sum('amount'));

        $data['rows'][$index]['c'][0]['v'] = Crypt::decrypt($category->name);
        $data['rows'][$index]['c'][1]['v'] = $sum;
        $data['rows'][$index]['c'][2]['v'] = ($prev_transactions + $prev_transfers);
        $index++;
      }
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

}