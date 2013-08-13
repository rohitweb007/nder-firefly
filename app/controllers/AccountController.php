<?php

use Carbon\Carbon as Carbon;

class AccountController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function addAccount() {
    return View::make('accounts.add');
  }

  public function showAllChart() {
    $key = cacheKey('Accounts', 'showAllChart');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data     = array(
          'cols' => array(
              array(
                  'id'    => 'date',
                  'label' => 'Date',
                  'type'  => 'date',
                  'p'     => array('role' => 'domain')
              ),
              array(
                  'id'    => 'balance',
                  'label' => 'Combined balance',
                  'type'  => 'number',
                  'p'     => array('role' => 'data')
              ),
          ),
          'rows' => array()
      );
      $today    = self::getLast();
      $past     = clone $today;
      $past->subMonths(4);
      $accounts = Auth::user()->accounts()->get();
      $index    = 0;
      while ($past <= $today) {
        $sum = 0;
        foreach ($accounts as $account) {
          $sum += $account->balance($past);
        }
        $month                             = intval($past->format('n')) - 1;
        $data['rows'][$index]['c'][0]['v'] = 'Date(' . intval($past->format('Y')) . ', ' . $month . ', ' . intval($past->format('j')) . ')';
        $data['rows'][$index]['c'][1]['v'] = $sum;
        $index++;
        $past->addDay();
      }
      Cache::put($key, $data, 1440);
    }
    return Response::json($data);
  }

  public function showAll() {
    $key = cacheKey('Accounts', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data     = array();
      $accounts = Auth::user()->accounts()->orderBy('id', 'ASC')->get();
      foreach ($accounts as $a) {
        $account = array(
            'id'      => intval($a->id),
            'name'    => Crypt::decrypt($a->name),
            'balance' => $a->balance()
        );
        $data[]  = $account;
      }
      Cache::put($key, $data, 1440);
    }
    return View::make('accounts.all')->with('data', $data);
  }



  public function newAccount() {

    $account                 = new Account;
    $account->name           = Input::get('name');
    $account->balance        = floatval(Input::get('balance'));
    $account->date           = Input::get('date');
    $account->fireflyuser_id = Auth::user()->id;

    // dit hele blok kan naar een listener
    $validator = Validator::make($account->toArray(), Account::$rules);
    $validator->fails();
    if ($validator->fails()) {
      //DB::delete('delete from cache'); // moet beter!
      return Redirect::to('/home/account/add')->withErrors($validator)->withInput();
    } else {
      $account->name = Crypt::encrypt($account->name);
      $account->save();
      return Redirect::to('/home');
      exit;
    }
  }

  public function homeOverviewChart($id = 0) {
    $key = cacheKey('Account', 'homeOverviewChart', $id, Session::get('period'));

    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    // 30 days into the past.
    $today         = new Carbon(Session::get('period')->format('Y-m-d'));
    // we do some fixing in case we're in the future:
    $actuallyToday = new Carbon('now');
    if ($today > $actuallyToday) {
      $today->modify('last day of this month');
    }
    $past    = clone $today;
    $past->subDays(30);
    $account = Auth::user()->accounts()->find($id);

    $data  = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'date',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'balance',
                'label' => 'Balance',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
        ),
        'rows' => array()
    );
    $bdp_q = $account->balancedatapoints()->where('date', '>=', $past->format('Y-m-d'))->where('date', '<=', $today->format('Y-m-d'))->get();
    $bdp   = array();
    foreach ($bdp_q as $b) {
      $bdp[$b->date] = floatval($b->balance);
    }
    $index = 0;
    while ($past <= $today) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
      $balance                           = isset($bdp[$past->format('Y-m-d')]) ? $bdp[$past->format('Y-m-d')] : null;
      $data['rows'][$index]['c'][1]['v'] = $balance;
      $past->add(new DateInterval('P1D'));
      $index++;
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  public function deleteAccount($id) {
    $a = Auth::user()->accounts()->find($id);
    if ($a) {
      $a->delete();
      return Redirect::to('/home');
    } else {
      return App::abort(404);
    }
  }

  public function editAccount($id) {

    $account = Auth::user()->accounts()->find($id);
    if ($account) {
      return View::make('accounts.edit')->with('account', $account);
    } else {
      return App::abort(404);
    }
  }

  public function doEditAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if ($account) {
      $account->name    = Input::get('name');
      $account->balance = floatval(Input::get('balance'));
      $account->date    = Input::get('date');
      $validator        = Validator::make($account->toArray(), Account::$rules);
      $validator->fails();
      if ($validator->fails()) {
        return Redirect::to('/home/account/edit/' . $account->id)->withErrors($validator)->withInput();
      } else {
        $account->name = Crypt::encrypt($account->name);
        $account->save();

        return Redirect::to('/home');
      }
    } else {
      return App::abort(404);
    }
  }



  /*

    public function getAccountSummary($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
    return App::abort(404);
    } else {
    $start = new Carbon(Input::get('start'));
    $end   = new Carbon(Input::get('end'));
    $key   = cacheKey('accountsummary', $id, $start, $end);
    if (Cache::has($key)) {
    return Response::json(Cache::get($key));
    }
    $startStr = $start->format('jS M Y');
    $endStr   = $end->format('jS M Y');

    $earned_raw = $account->transactions()->where('amount', '>', 0)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount') * 1;
    $earned     = mf($earned_raw);

    $spent_raw    = $account->transactions()->where('amount', '<', 0)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount') * -1;
    $spent        = mf($spent_raw);
    $net          = mf($earned_raw - $spent_raw);
    $profitloss   = ($earned_raw - $spent_raw) < 0 ? 'loss' : 'profit';
    $moved_raw    = floatval($account->transfersfrom()->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
    $moved        = mf($moved_raw);
    $received_raw = floatval($account->transfersto()->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));

    $received        = mf($received_raw);
    $totalmoved      = mf($received_raw - $moved_raw < 0 ? ($received_raw - $moved_raw) * -1 : $received_raw - $moved_raw);
    $moved_away_here = ($received_raw - $moved_raw) > 0 ? 'to this account' : 'away from this account';
    $avg_raw         = ($earned_raw - $spent_raw) / $end->format('t');
    $avg             = mf($avg_raw);
    $ms              = $avg_raw > 0 ? 'made' : 'spent';

    $string    = 'In the period between %s and %s (including), you have
    made a net %s of %s (earned %s and spent %s).<br />

    You have moved %s away and received %s from other accounts, effectively transferring %s %s.
    On average per day, you have %s %s.';
    $formatted = sprintf($string, $startStr, $endStr, $profitloss, $net, $earned, $spent, $moved, $received, $totalmoved, $moved_away_here, $ms, $avg);
    Cache::put($key, $formatted, 1440);
    return Response::json($formatted);
    }
    }
    public function showTransactionsInTimeframe($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
    return App::abort(404);
    } else {
    $start = new Carbon(Input::get('start'));
    $end   = new Carbon(Input::get('end'));

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
    $date                              = new Carbon($t->date);
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
    public function showBeneficiariesInTimeframe($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
    return App::abort(404);
    } else {
    $start = new Carbon(Input::get('start'));
    $end   = new Carbon(Input::get('end'));
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


    public function showMovesInTimeframe($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
    return App::abort(404);
    } else {

    $results = array();
    $start   = new Carbon(Input::get('start'));
    $end     = new Carbon(Input::get('end'));
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

    public function showCategoriesInTimeframe($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
    return App::abort(404);
    } else {
    $start = new Carbon(Input::get('start'));
    $end   = new Carbon(Input::get('end'));
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

    public function showBudgetsInTimeframe($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
    return App::abort(404);
    } else {
    $start = new Carbon(Input::get('start'));
    $end   = new Carbon(Input::get('end'));

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
    $date         = new Carbon($budget->date);
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
   */
}