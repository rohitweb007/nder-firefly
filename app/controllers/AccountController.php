<?php

class AccountController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function addAccount() {
    return View::make('accounts.add');
  }

  public function showAccountOverview($id) {
    $account = Auth::user()->accounts()->find($id);
    if($account) {
      return View::make('accounts.overview')->with('account',$account);
    } else {
      return Response::error(404);
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
    $key = cacheKey('Account','homeOverviewGraph',$id,CACHE_TODAY);
    if(Cache::has($key)) {
      return Response::json(Cache::get($key));
    }
    // 30 days into the past.
    $today   = clone Session::get('period');
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
      $data['rows'][$index]['c'][0]['v'] = $past->format('d M');
      $balance                           = $account->balance($past);
      if ($balance >= 0) {
        $data['rows'][$index]['c'][1]['v'] = $balance;
        $data['rows'][$index]['c'][2]['v'] = NULL;
      } else {
        $data['rows'][$index]['c'][1]['v'] = NULL;
        $data['rows'][$index]['c'][2]['v'] = $balance;
      }

      $past->add(new DateInterval('P1D'));
      $index++;
    }
    Cache::put($key,$data,1440);
    return Response::json($data);
  }

  /**
   * Same but a longer date range
   * TODO combine and smarter call.
   * @param type $id
   * @return type
   */
  public function overviewGraph($id = 0) {
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
      $month = intval($past->format('n'))-1;
      $year = intval($past->format('Y'));
      $day = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date('.$year.', '.$month.', '.$day.')';
      $balance                           = $account->balance($past);
      $data['rows'][$index]['c'][1]['v'] = $balance;
      $past->add(new DateInterval('P1D'));
      $index++;
    }
    return Response::json($data);
  }

}