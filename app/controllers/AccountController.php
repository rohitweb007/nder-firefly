<?php

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
      $past->sub(new DateInterval('P4M'));
      $accounts = Auth::user()->accounts()->get();
      $index    = 0;
      while ($past <= $today) {
        $sum = 0;
        foreach ($accounts as $account) {
          $sum += $account->balance($past);
        }
        $month = intval($past->format('n')) - 1;
        $year  = intval($past->format('Y'));
        $day   = intval($past->format('j'));

        $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
        $data['rows'][$index]['c'][1]['v'] = $sum;
        $index++;
        $past->add(new DateInterval('P1D'));
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
        $account                = array(
            'id'    => intval($a->id),
            'name'  => Crypt::decrypt($a->name),
            'start' => floatval($a->balance),
        );
        $date                   = new DateTime($a->date);
        $account['startdate']   = $date->format('j F Y');
        $now                    = new DateTime('now');
        $account['current']     = $a->balance($now);
        $account['currentdate'] = $now->format('j F Y');

        // now it gets tougher.
        // what's the average's account's balance, and how does it increase / decrease every month?
        $firstDate = self::getFirst($a->id);
        $firstDate->modify('first day of this month');
        $lastDate  = self::getLast($a->id);
        $lastDate->modify('first day of this month');

        // now for every month we get the differences:
        $diffs   = array();
        $balance = $a->balance;
        while ($firstDate < $lastDate) {
          $currentBalance = $a->balance($firstDate);
          $diff           = $currentBalance - $balance;
          $diffs[]        = $diff;
          $firstDate->add(new DateInterval('P1M'));
          $balance        = $currentBalance;
        }
        if (count($diffs) == 0) {
          $avg = 0;
        } else {
          $avg = count($diffs) > 0 ? array_sum($diffs) / count($diffs) : $diffs[0];
        }
        $account['avg'] = $avg;
        $data[]         = $account;
      }
      Cache::put($key, $data, 1440);
    }
    return View::make('accounts.all')->with('data', $data);
  }

  public function showAccountOverview($id) {
    $account = Auth::user()->accounts()->find($id);
    if ($account) {
      return View::make('accounts.overview')->with('account', $account);
    } else {
      return App::abort(404);
    }
  }

  public function newAccount() {

    $account                 = new Account;
    $account->name           = Input::get('name');
    $account->balance        = floatval(Input::get('balance'));
    $account->date           = Input::get('date');
    $account->fireflyuser_id = Auth::user()->id;

    $validator = Validator::make($account->toArray(), Account::$rules);
    $validator->fails();
    if ($validator->fails()) {
      //DB::delete('delete from cache'); // moet beter!
      return Redirect::to('/home/account/add')->withErrors($validator)->withInput();
    } else {
      $account->name = Crypt::encrypt($account->name);
      $account->save();
      Cache::flush();
      Session::flash('success', 'The new account has been created.');
      return Redirect::to('/home');
      exit;
    }
  }

  public function homeOverviewGraph($id = 0) {
    $key = cacheKey('Account', 'homeOverviewGraph', $id, Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    // 30 days into the past.
    $today         = clone Session::get('period');
    // we do some fixing in case we're in the future:
    $actuallyToday = new DateTime('now');
    if ($today > $actuallyToday) {
      $today->modify('last day of this month');
    }
    $past    = clone $today;
    $past->sub(new DateInterval('P30D'));
    $account = Auth::user()->accounts()->find($id);

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
                'label' => 'Balance',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
        ),
        'rows' => array()
    );

    $index = 0;
    while ($past <= $today) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
      $balance                           = $account->balance($past);
      $data['rows'][$index]['c'][1]['v'] = $balance;
      $past->add(new DateInterval('P1D'));
      $index++;
    }
    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

  /**
   * Same but a longer date range
   * TODO combine and smarter call.
   * @param type $id
   * @return type
   */
  public function overviewGraph($id = 0) {
    $key = cacheKey('Account', 'overviewGraph', $id, Session::get('period'));
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    $today   = clone Session::get('period');
    $end     = clone($today);
    $end->modify('last day of this month');
    $past    = self::getFirst();
    $account = Auth::user()->accounts()->find($id);

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
                'label' => 'Balance',
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
    $balance = $account->balance($past);
    while ($past <= $end) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';



      if ($past > $today) {
        $prediction = $account->predict($past);
        $balance    = $balance - $prediction;
        $certain    = false;
      } else {
        $balance = $account->balance($past);
        $certain = true;
      }
      $data['rows'][$index]['c'][1]['v'] = $balance;
      $data['rows'][$index]['c'][2]['v'] = $certain;
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
      Cache::flush();
      Session::flash('success', 'Account deleted');
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
        Cache::flush();
        Session::flash('success', 'The account has been edited.');
        return Redirect::to('/home');
      }
    } else {
      return App::abort(404);
    }
  }

  public function getAccountSummary($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));
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
      $totalmoved      = mf($received_raw - $moved_raw < 0 ? ($received_raw - $moved_raw) * -1 : $received_raw - $moved_raws);
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

  public function showBeneficiariesInTimeframe($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return App::abort(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));
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

}