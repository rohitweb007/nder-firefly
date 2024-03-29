<?php

use Carbon\Carbon as Carbon;

class TransferController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    $key = cacheKey('Transfers', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data   = array();
      $transf = Auth::user()->transfers()->
                      leftJoin('categories', 'categories.id', '=', 'category_id')->
                      leftJoin('accounts as af', 'af.id', '=', 'account_from')->
                      leftJoin('accounts as at', 'at.id', '=', 'account_to')->
                      leftJoin('budgets', 'budgets.id', '=', 'budget_id')->
                      leftJoin('targets', 'targets.id', '=', 'target_id')->
                      orderBy('transfers.date', 'DESC')->orderBy('transfers.created_at', 'DESC')->get(
              array(
                  'transfers.id',
                  'category_id', 'categories.name AS category_name',
                  'account_to', 'at.name AS account_to_name',
                  'account_from', 'af.name AS account_from_name',
                  'budget_id', 'budgets.name AS budget_name',
                  'target_id', 'targets.description AS target_description',
                  'transfers.date', 'transfers.description', 'transfers.amount'
              )
      );

      foreach ($transf as $t) {
        $month             = new Carbon($t->date);
        $strMonth          = $month->format('F Y');
        $data[$strMonth]   = isset($data[$strMonth]) ? $data[$strMonth] : array();
        $strDate           = $month->format('d F Y');
        $current           = array(
            'id'                 => intval($t->id),
            'date'               => $strDate,
            'description'        => Crypt::decrypt($t->description),
            'amount'             => mf(floatval($t->amount)),
            'account_to'         => $t->account_to,
            'account_to_name'    => Crypt::decrypt($t->account_to_name),
            'account_from'       => $t->account_from,
            'account_from_name'  => Crypt::decrypt($t->account_from_name),
            'budget_id'          => $t->budget_id,
            'budget_name'        => (is_null($t->budget_id) ? null : Crypt::decrypt($t->budget_name)),
            'target_id'          => $t->target_id,
            'target_description' => (is_null($t->target_id) ? null : Crypt::decrypt($t->target_description)),
            'category_id'        => $t->category_id,
            'category_name'      => (is_null($t->category_id) ? null : Crypt::decrypt($t->category_name)),
        );
        $data[$strMonth][] = $current;
      }
      //Cache::put($key, $data, 4000);
    }
    return View::make('transfers.all')->with('transfers', $data);
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
    if (strlen(Input::get('category')) > 0) {
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
    if (strlen(Input::get('target')) > 0) {
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

    if ($validator->fails()) {
      return Redirect::to('/home/transfer/add')->withErrors($validator)->withInput();
    } else {
      $transfer->save();
      return Redirect::to('/home');
    }
  }

  public function deleteTransfer($id) {
    $tr = Auth::user()->transfers()->find($id);
    if ($tr) {
      $tr->delete();
      return Redirect::to('/home');
    } else {
      return Response::error(404);
    }
  }

  public function editTransfer($id) {
    $transfer = Auth::user()->transfers()->find($id);
    if ($transfer) {
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

      return View::make('transfers.edit')->with('targets', $targets)->with('accounts', $accounts)->with('budgets', $budgets)->with('categories', $categories)->with('transfer', $transfer);
    } else {
      App::abort(404);
    }
  }

  public function doEditTransfer($id) {

    $transfer = Auth::user()->transfers()->find($id);
    if ($transfer) {
      $transfer->amount           = floatval(Input::get('amount'));
      $transfer->description      = Input::get('description');
      $transfer->date             = Input::get('date');

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
      if (strlen(Input::get('category')) > 0) {
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
      } else {
        $transfer->category_id = null;
      }
      // budget (special)
      if (intval(Input::get('budget')) > 0) {
        $budget = Auth::user()->budgets()->find(intval(Input::get('budget')));
        if (!is_null($budget)) {
          $transfer->budget_id = $budget->id;
        }
      }
      // target (special)
      if (strlen(Input::get('target')) > 0) {
        $targets = Auth::user()->targets()->get(); //->where('name','=',Input::get('category'))->first();
        $target  = null;
        foreach ($targets as $t) {
          if (Crypt::decrypt($t->description) == Input::get('target')) {
            $transfer->target_id = $t->id;
            break;
          }
        }
        unset($targets, $t);
      } else {
        $transfer->target_id = null;
      }




      $validator             = Validator::make($transfer->toArray(), Transfer::$rules);
      $transfer->description = Crypt::encrypt($transfer->description);

      if ($validator->fails()) {
        return Redirect::to('/home/transfer/edit/' . $transfer->id)->withErrors($validator)->withInput();
      } else {
        $transfer->save();
        return Redirect::to('/home');
      }
    } else {
      App::abort(404);
    }
  }

}