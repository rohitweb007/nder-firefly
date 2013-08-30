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

  public function homeOverviewChart($id = 0, $date = null) {
    $date = is_null($date) ? Session::get('period') : $date;
    $key  = cacheKey('Account', 'homeOverviewChart', $id, $date);

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

}