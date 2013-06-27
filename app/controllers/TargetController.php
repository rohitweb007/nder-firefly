<?php

class TargetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function homeOverviewGraph($id = 0) {
    $target = Auth::user()->targets()->find($id);
    if ($target) {
      $key = cacheKey('Target', 'homeOverviewGraph', $id, CACHE_TODAY);
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      } else {
        $data = array(
            'cols' => array(
                array(
                    'id'    => 'date',
                    'label' => 'Date',
                    'type'  => 'string',
                    'p'     => array('role' => 'domain')
                ),
                array(
                    'id'    => 'guideline',
                    'label' => 'Guideline',
                    'type'  => 'number',
                    'p'     => array('role' => 'data')
                ),
                array(
                    'id'    => 'saved',
                    'label' => 'Saved',
                    'type'  => 'number',
                    'p'     => array('role' => 'data')
                )
            ),
            'rows' => array()
        );
        $start = new DateTime($target->startdate);
        if($target->duedate == '0000-00-00') {
          $end = new DateTime('today');
        } else {
          $end = new DateTime($target->duedate);
        }
        $current = clone($start);
        $index = 0;
        $guide = 0;
        $step = $target->guide($start,true);
        while($current <= $end) {
          $data['rows'][$index]['c'][0]['v'] = $current->format('d F');
          $data['rows'][$index]['c'][1]['v'] = $guide;
          $data['rows'][$index]['c'][2]['v'] = $target->hassaved($current);
          $current->add(new DateInterval('P1D'));
          $guide += $step;
          $index++;
        }
        return Response::json($data);
      }
    } else {
      return Response::error(404);
    }
  }

  public function addTarget() {

    $accounts = array();
    foreach (Auth::user()->accounts()->get() as $account) {
      $accounts[$account->id] = Crypt::decrypt($account->name);
    }

    return View::make('targets.add')->with('accounts', $accounts);
  }

  public function newTarget() {


    $target                 = new Target;
    $target->amount         = floatval(Input::get('amount'));
    $target->description    = Input::get('description');
    $target->fireflyuser_id = Auth::user()->id;
    $target->duedate        = Input::get('duedate');
    $target->startdate      = Input::get('startdate');

    if (!is_null(Input::get('account'))) {
      $account = Auth::user()->accounts()->find(Input::get('account'));
      if (!is_null($account)) {
        $target->account_id = $account->id;
      }
    }

    $validator           = Validator::make($target->toArray(), Target::$rules);
    $target->description = Crypt::encrypt($target->description);

    if ($validator->fails()) {
      return Redirect::to('/home/target/add')->withErrors($validator)->withInput();
    } else {
      $target->save();
      Session::flash('success', 'The new target has been created.');
      return Redirect::to('/home');
    }
  }

}