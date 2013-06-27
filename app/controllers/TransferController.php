<?php

class TransferController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    return View::make('transfers.all')->with('transfers',Auth::user()->transfers()->orderBy('date','DESC')->get());
  }

  public function addTransfer() {

    $accounts = array();
    foreach (Auth::user()->accounts()->get() as $account) {
      $accounts[$account->id] = Crypt::decrypt($account->name);
    }

    $budgets    = array();
    $budgets[0] = '(no budget)';
    foreach (Auth::user()->budgets()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->get() as $budget) {
      $budgets[$budget->id] = Crypt::decrypt($budget->name);
    }

    $categories = array();
    foreach (Auth::user()->categories()->get() as $cat) {
      $categories[] = Crypt::decrypt($cat->name);
    }

    $targets = array();
    foreach (Auth::user()->targets()->get() as $t) {
      $targets[] = Crypt::decrypt($t->description);
    }

    return View::make('transfers.add')->with('targets', $targets)->with('accounts', $accounts)->with('budgets', $budgets)->with('categories', $categories);
  }

  public function newTransfer() {

    $transfer                   = new Transfer;
    $transfer->amount           = floatval(Input::get('amount'));
    $transfer->description      = Input::get('description');
    $transfer->fireflyuser_id   = Auth::user()->id;
    $transfer->date             = Input::get('date');
    $transfer->ignoreprediction = Input::get('ignoreprediction') == 'on' ? 1 : 0;
    $transfer->countasexpense = Input::get('countasexpense') == 'on' ? 1 : 0;

    // account_from (special)
    if (!is_null(Input::get('account_from'))) {
      $account = Auth::user()->accounts()->find(Input::get('account_from'));
      if (!is_null($account)) {
        $transfer->account_from = $account->id;
      }
    }
    // account_to (special)
    if (!is_null(Input::get('account_to'))) {
      $account = Auth::user()->accounts()->find(Input::get('account_to'));
      if (!is_null($account)) {
        $transfer->account_to = $account->id;
      }
    }
    // category (special)
    if (!is_null(Input::get('category'))) {
      $categories = Auth::user()->categories()->get(); //->where('name','=',Input::get('category'))->first();
      $category   = null;
      foreach ($categories as $cat) {
        if (Crypt::decrypt($cat->name) == Input::get('category')) {
          $category = $cat;
          break;
        }
      }
      unset($cat, $categories);
      if (is_null($category)) {

        $category                 = new Category;
        $category->fireflyuser_id = Auth::user()->id;
        $category->name           = Input::get('category');
        $category->showtrend      = 0;
        $category->icon_id        = Icon::first()->id;
        $validator                = Validator::make($category->toArray(), Category::$rules);
        if ($validator->passes()) {
          $category->name        = Crypt::encrypt($category->name);
          $category->save();
          $transfer->category_id = $category->id;
        }
      } else {
        $transfer->category_id = $category->id;
      }
    }
    // budget (special)
    if (intval(Input::get('budget')) > 0) {
      $budget = Auth::user()->budgets()->find(intval(Input::get('budget')));
      if (!is_null($budget)) {
        $transfer->budget_id = $budget->id;
      }
    }
    // target (special)
    if (!is_null(Input::get('target'))) {
      $targets = Auth::user()->targets()->get(); //->where('name','=',Input::get('category'))->first();
      $target  = null;
      foreach ($targets as $t) {
        if (Crypt::decrypt($t->description) == Input::get('target')) {
          $transfer->target_id = $t->id;
          break;
        }
      }
      unset($targets, $t);
    }




    $validator             = Validator::make($transfer->toArray(), Transfer::$rules);
    $transfer->description = Crypt::encrypt($transfer->description);

//    var_dump($validator->passes());
//    var_dump($validator->messages());
//    var_dump($transfer);
//    exit;

    if ($validator->fails()) {
      return Redirect::to('/home/transfer/add')->withErrors($validator)->withInput();
    } else {
      $transfer->save();
      Session::flash('success', 'The new transfer has been created.');
      return Redirect::to('/home');
    }
  }

}