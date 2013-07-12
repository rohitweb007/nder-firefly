<?php

class TargetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function doEditTarget($id) {
    $target = Auth::user()->targets()->find($id);
    if ($target) {
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
        Cache::flush();
        $target->save();
        Session::flash('success', 'Changes to target saved!');
        return Redirect::to('/home');
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteTarget($id) {
    $t = Auth::user()->targets()->find($id);
    if ($t) {
      $t->delete();
      Cache::flush();
      Session::flash('success', 'Target deleted');
      return Redirect::to('/home');
    } else {
      return App::abort(404);
    }
  }

  public function editTarget($id) {
    $target = Auth::user()->targets()->find($id);
    if ($target) {
      $accounts = array();
      foreach (Auth::user()->accounts()->get() as $account) {
        $accounts[$account->id] = Crypt::decrypt($account->name);
      }
      return View::make('targets.edit')->with('target', $target)->with('accounts', $accounts);
    } else {
      return App::abort(404);
    }
  }

  public function showAll() {
    $key = cacheKey('Targets', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data    = array();
      $targets = Auth::user()->targets()->get();
      foreach ($targets as $t) {
        $daily  = $t->guide(null, false);
        $left   = floatval($t->amount) - $t->hassaved();
        $start  = new DateTime($t->startdate);
        $due    = $t->duedate == null ? null : new DateTime($t->duedate);
        $duestr = is_null($due) ? '-' : $due->format('j F Y');
        $target = array(
            'id'          => $t->id,
            'description' => Crypt::decrypt($t->description),
            'amount'      => mf($t->amount),
            'current'     => mf($t->hassaved()),
            'should'      => mf($t->shouldhavesaved()),
            'daily'       => $daily < $left ? mf($daily) : mf(0),
            'weekly'      => $daily * 7 < $left ? mf($daily * 7) : mf(0),
            'monthly'     => $daily * 31 < $left ? mf($daily * 31) : mf(0),
            'start'       => $start->format('j F Y'),
            'due'         => $duestr
        );
        $data[] = $target;
      }
      //Cache::put($key, $data, 1440);
    }
    return View::make('targets.all')->with('data', $data);
  }

  public function homeOverviewGraph($id = 0) {
    $target = Auth::user()->targets()->find($id);
    if ($target) {
      $key = cacheKey('Target', 'homeOverviewGraph', $id, Session::get('period'));
      if (Cache::has($key)) {
        return Response::json(Cache::get($key));
      } else {
        $data  = array(
            'cols' => array(
                array(
                    'id'    => 'date',
                    'label' => 'Date',
                    'type'  => 'date',
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
        if ($target->duedate == '0000-00-00') {
          $end = new DateTime('today');
        } else {
          $end = new DateTime($target->duedate);
        }
        $current = clone($start);
        $index   = 0;
        $guide   = 0;
        $step    = $target->guide($start, true);
        while ($current <= $end) {
          $month                             = intval($current->format('n')) - 1;
          $year                              = intval($current->format('Y'));
          $day                               = intval($current->format('j'));
          $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
          $data['rows'][$index]['c'][1]['v'] = $guide;
          $data['rows'][$index]['c'][2]['v'] = $target->hassaved($current);
          $current->add(new DateInterval('P1D'));
          $guide += $step;
          $index++;
        }
        Cache::put($key, $data, 1440);
        return Response::json($data);
      }
    } else {
      return App::abort(404);
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
      Cache::flush();
      $target->save();
      Session::flash('success', 'The new target has been created.');
      return Redirect::to('/home');
    }
  }

}