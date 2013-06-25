<?php

class TargetController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function addTarget() {

    $accounts = array();
    foreach (Auth::user()->accounts()->get() as $account) {
      $accounts[$account->id] = Crypt::decrypt($account->name);
    }

    return View::make('targets.add')->with('accounts', $accounts);
  }

  public function newTarget() {


    $target = new Target;
    $target->amount = floatval(Input::get('amount'));
    $target->description = Input::get('description');
    $target->fireflyuser_id = Auth::user()->id;
    $target->duedate = Input::get('duedate');
    $target->startdate = Input::get('startdate');

    if (!is_null(Input::get('account'))) {
      $account = Auth::user()->accounts()->find(Input::get('account'));
      if (!is_null($account)) {
        $target->account_id = $account->id;
      }
    }

    $validator                = Validator::make($target->toArray(), Target::$rules);
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