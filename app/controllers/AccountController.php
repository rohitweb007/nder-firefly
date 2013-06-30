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
    $key = cacheKey('Budgets', 'showAll');
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
                'type'  => 'string',
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
      $data['rows'][$index]['c'][0]['v'] = $past->format('d M');
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
    // 30 days into the past.
    $today   = clone Session::get('period');
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
                'id'    => 'lowbalance',
                'label' => 'Balance',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            )
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
      $account->name           = Input::get('name');
      $account->balance        = floatval(Input::get('balance'));
      $account->date           = Input::get('date');
      $validator = Validator::make($account->toArray(), Account::$rules);
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

}