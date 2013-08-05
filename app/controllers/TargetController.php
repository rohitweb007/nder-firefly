<?php

use Carbon\Carbon as Carbon;

class TargetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showOverview($id) {
    $target = Auth::user()->targets()->find($id);
    if ($target) {
      $key = cacheKey('overview', 'targets', $id);
      if (Cache::has($key)) {
        $data = Cache::get($key);
      } else {
        $data = array();
        $transfers = $target->transfers()->
                        leftJoin('accounts AS a1', 'a1.id', '=', 'account_from')->
                        leftJoin('accounts AS a2', 'a2.id', '=', 'account_to')->
                        leftJoin('categories AS c', 'c.id', '=', 'category_id')->
                        leftJoin('budgets AS b', 'b.id', '=', 'budget_id')->
                        orderBy('date','DESC')->get(array(
            'transfers.id', 'account_from', 'a1.name as account_from_name', 'account_to', 'a2.name as account_to_name',
            'category_id', 'c.name as category_name', 'budget_id', 'b.name as budget_name', 'description',
            'transfers.amount', 'transfers.date', 'ignoreprediction', 'countasexpense'
        ));
        foreach ($transfers as $t) {
          $arr = array(
              'id' => $t->id,
              'account_from' => $t->account_from,
              'account_from_name' => is_null($t->account_from_name) ? null : Crypt::decrypt($t->account_from_name),
              'account_to' => $t->account_to,
              'account_to_name' => is_null($t->account_to_name) ? null: Crypt::decrypt($t->account_to_name),
              'category_id' => $t->category_id,
              'category_name' => is_null($t->category_name) ? null : Crypt::decrypt($t->category_name),
              'budget_id' => $t->budget_id,
              'budget_name' => is_null($t->budget_name) ? null : Crypt::decrypt($t->budget_name),
              'description' => Crypt::decrypt($t->description),
              'amount' => $t->amount,
              'date' => new Carbon($t->date),
              'ignoreprediction' => intval($t->ignoreprediction) == 1 ? true : false,
              'countasexpense' => intval($t->countasexpense) == 1 ? true : false,
          );
          $data[] = $arr;
        }
      }

      return View::make('targets.overview')->with('target', $target)->with('transfers', $data);
    }
    App::abort(404);
  }

  public function doEditTarget($id) {
    $target = Auth::user()->targets()->find($id);
    if ($target) {
      $target->amount         = floatval(Input::get('amount'));
      $target->description    = Input::get('description');
      $target->fireflyuser_id = Auth::user()->id;
      $target->duedate        = is_null(Input::get('duedate')) || Input::get('duedate') == '' ? null : Input::get('duedate');
      $target->startdate      = Input::get('startdate');
      $target->closed         = Input::get('closed') == 'on' ? 1 : 0;

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
        return Redirect::to('/home/targets');
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteTarget($id) {
    $t = Auth::user()->targets()->find($id);
    if ($t) {
      $t->delete();
      return Redirect::to('/home/targets');
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
      $targets = Auth::user()->targets()->orderBy('closed')->get();
      foreach ($targets as $t) {
        $daily  = $t->guide(null, false);
        $left   = floatval($t->amount) - $t->hassaved();
        $start  = new Carbon($t->startdate);
        $due    = $t->duedate == null ? null : new Carbon($t->duedate);
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
            'due'         => $duestr,
            'closed'      => intval($t->closed) == 1 ? true : false
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
        $start = new Carbon($target->startdate);
        if ($target->duedate == '0000-00-00') {
          $end = new Carbon('today');
        } else {
          $end = new Carbon($target->duedate);
        }

        $current = clone($start);
        $index   = 0;
        $guide   = 0;
        $step    = $target->guide($start, true);

        // transfers
        $transfers_q = $target->transfers()->where('date', '<=', $end->format('Y-m-d'))->get();
        $transferred = array();
        foreach ($transfers_q as $t) {
          $transferred[$t->date] = isset($transferred[$t->date]) ? $transferred[$t->date] : 0;
          if ($t->account_from == $target->account_id) {
            $transferred[$t->date] -= floatval($t->amount);
          } else if ($t->account_to == $target->account_id) {
            $transferred[$t->date] += floatval($t->amount);
          }
        }
        $saved = 0;
        while ($current <= $end) {
          $saved += isset($transferred[$current->format('Y-m-d')]) ? $transferred[$current->format('Y-m-d')] : 0;
          $month                             = intval($current->format('n')) - 1;
          $year                              = intval($current->format('Y'));
          $day                               = intval($current->format('j'));
          $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';
          $data['rows'][$index]['c'][1]['v'] = $guide;
          $data['rows'][$index]['c'][2]['v'] = $saved; // $target->hassaved($current);
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
    $target->closed         = 0;

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
      return Redirect::to('/home');
    }
  }

}