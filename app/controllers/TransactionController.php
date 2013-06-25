<?php

class TransactionController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function addTransaction() {

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

    $beneficiaries = array();
    foreach (Auth::user()->beneficiaries()->get() as $ben) {
      $beneficiaries[] = Crypt::decrypt($ben->name);
    }

    return View::make('transactions.add')->with('accounts', $accounts)->with('budgets', $budgets)
                    ->with('categories', $categories)
                    ->with('beneficiaries', $beneficiaries);
  }

  public function newTransaction() {
    $transaction                 = new Transaction;
    $transaction->amount         = floatval(Input::get('amount'));
    $transaction->fireflyuser_id = Auth::user()->id;
    $transaction->date           = Input::get('date');
    $transaction->onetime        = Input::get('onetime') == 'on' ? 1 : 0;
    $transaction->description    = Input::get('description');

    if (Input::get('type') == 'min') {
      $transaction->amount = $transaction->amount * -1;
    }

    if (!is_null(Input::get('account'))) {
      $account = Auth::user()->accounts()->find(Input::get('account'));
      if (!is_null($account)) {
        $transaction->account_id = $account->id;
      }
    }

    // budget
    if (intval(Input::get('budget')) > 0) {
      $budget = Auth::user()->budgets()->find(intval(Input::get('budget')));
      if (!is_null($budget)) {
        $transaction->budget_id = $budget->id;
      }
    }

    // category
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
        $category->icon_id        = Icon::first()->id; // FIXME moet niet hardcoded
        $validator                = Validator::make($category->toArray(), Category::$rules);
        if ($validator->passes()) {
          $category->name           = Crypt::encrypt($category->name);
          $category->save();
          $transaction->category_id = $category->id;
        }
      } else {
        $transaction->category_id = $category->id;
      }
    }


    // beneficiary

    if (!is_null(Input::get('beneficiary'))) {
      $beneficiaries  = Auth::user()->beneficiaries()->get(); //->where('name','=',Input::get('beneficiary'))->first();
      $beneficiary = null;
      foreach ($beneficiaries as $ben) {
        if (Crypt::decrypt($ben->name) == Input::get('beneficiary')) {
          $beneficiary = $ben;
          break;
        }
      }
      unset($ben, $categories);
      if (is_null($beneficiary)) {

        $beneficiary                 = new Beneficiary;
        $beneficiary->fireflyuser_id = Auth::user()->id;
        $beneficiary->name           = Input::get('beneficiary');
        $validator                   = Validator::make($beneficiary->toArray(), Beneficiary::$rules);
        if ($validator->passes()) {
          $beneficiary->name           = Crypt::encrypt($beneficiary->name);
          $beneficiary->save();
          $transaction->beneficiary_id = $beneficiary->id;
        }
      } else {
        $transaction->beneficiary_id = $beneficiary->id;
      }
    }

    $validator                = Validator::make($transaction->toArray(), Transaction::$rules);
    $transaction->description = Crypt::encrypt($transaction->description);

    if ($validator->fails()) {
      return Redirect::to('/home/transaction/add')->withErrors($validator)->withInput();
    } else {
      $transaction->save();
      Session::flash('success', 'The new transaction has been created.');
      return Redirect::to('/home');
    }
  }

}