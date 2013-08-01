<?php

use Holmes\Holmes;
use Carbon\Carbon as Carbon;

class TransactionController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    $key = cacheKey('Transactions', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data = array();


      $trans = Auth::user()->transactions()->
                      leftJoin('accounts', 'accounts.id', '=', 'account_id')->
                      leftJoin('budgets', 'budgets.id', '=', 'budget_id')->
                      leftJoin('categories', 'categories.id', '=', 'category_id')->
                      leftJoin('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_id')->
                      orderBy('transactions.date', 'DESC')->get(array(
          'transactions.id',
          'account_id', 'accounts.name AS account_name',
          'budget_id', 'budgets.name AS budget_name',
          'category_id', 'categories.name AS category_name',
          'beneficiary_id', 'beneficiaries.name AS beneficiary_name',
          'transactions.date', 'description', 'transactions.amount', 'onetime'
      ));


      foreach ($trans as $t) {

        $month = new Carbon($t->date);

        $strMonth        = $month->format('F Y');
        $data[$strMonth] = isset($data[$strMonth]) ? $data[$strMonth] : array();

        $date    = new Carbon($t->date);
        $strDate = $date->format('d F Y');

        $current = array(
            'id'               => intval($t->id),
            'date'             => $strDate,
            'description'      => Crypt::decrypt($t->description),
            'amount'           => mf(floatval($t->amount)),
            'onetime'          => $t->onetime == 1 ? true : false,
            'account_id'       => $t->account_id,
            'account_name'     => Crypt::decrypt($t->account_name),
            'budget_id'        => $t->budget_id,
            'budget_name'      => !is_null($t->budget_name) ? Crypt::decrypt($t->budget_name) : null,
            'beneficiary_id'   => $t->beneficiary_id,
            'beneficiary_name' => !is_null($t->beneficiary_name) ? Crypt::decrypt($t->beneficiary_name) : null,
            'category_id'      => $t->category_id,
            'category_name'    => !is_null($t->category_name) ? Crypt::decrypt($t->category_name) : null
        );

        $data[$strMonth][] = $current;
      }


      Cache::put($key, $data, 1440);
    }
    return View::make('transactions.all')->with('transactions', $data);
  }

  public function addTransaction() {

    $accounts = array();
    foreach (Auth::user()->accounts()->get() as $account) {
      $accounts[$account->id] = Crypt::decrypt($account->name);
    }

    $budgets    = array();
    $budgets[0] = '(no budget)';
    foreach (Auth::user()->budgets()->orderBy('date', 'DESC')->take(20)->get() as $budget) {
      $date                 = new Carbon($budget->date);
      $budgets[$budget->id] = Crypt::decrypt($budget->name) . ' (' . $date->format('F Y') . ')';
    }

    $categories = array();
    foreach (Auth::user()->categories()->get() as $cat) {
      $categories[] = Crypt::decrypt($cat->name);
    }

    $beneficiaries = array();
    foreach (Auth::user()->beneficiaries()->get() as $ben) {
      $beneficiaries[] = Crypt::decrypt($ben->name);
    }
    if (Holmes::isMobile()) {
      return View::make('mobile.transactions.add')->with('accounts', $accounts)->with('budgets', $budgets)
                      ->with('categories', $categories)
                      ->with('beneficiaries', $beneficiaries);
    } else {
      return View::make('transactions.add')->with('accounts', $accounts)->with('budgets', $budgets)
                      ->with('categories', $categories)
                      ->with('beneficiaries', $beneficiaries);
    }
  }

  public function massAddTransaction() {

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
    return View::make('transactions.massAdd')->with('accounts', $accounts)->with('budgets', $budgets)
                    ->with('categories', $categories)
                    ->with('beneficiaries', $beneficiaries);
  }

  public function massNewTransaction() {

    $data = Input::get('transactions');
    if (is_array($data)) {
      foreach ($data as $new) {
        // basic info:
        $transaction                 = new Transaction;
        $transaction->amount         = floatval($new['amount']);
        $transaction->fireflyuser_id = Auth::user()->id;
        $transaction->date           = $new['date'];
        $transaction->onetime        = (isset($new['onetime']) && $new['onetime'] == 'on') ? 1 : 0;
        $transaction->description    = $new['description'];

        // account ID:
        if (!is_null($new['account'])) {
          $account = Auth::user()->accounts()->find($new['account']);
          if (!is_null($account)) {
            $transaction->account_id = $account->id;
          }
        }

        // budget
        if (intval($new['budget']) > 0) {
          $budget = Auth::user()->budgets()->find(intval($new['budget']));
          if (!is_null($budget)) {
            $transaction->budget_id = $budget->id;
          }
        }

        // category
        if (strlen($new['category']) != 0) {
          $categories = Auth::user()->categories()->get(); //->where('name','=',Input::get('category'))->first();
          $category   = null;
          foreach ($categories as $cat) {
            if (Crypt::decrypt($cat->name) == $new['category']) {
              $category = $cat;
              break;
            }
          }
          unset($cat, $categories);
          if (is_null($category)) {

            $category                 = new Category;
            $category->fireflyuser_id = Auth::user()->id;
            $category->name           = $new['category'];
            $category->showtrend      = 0;
            $category->icon_id        = Icon::first()->id; // FIXME moet niet hardcoded
            $validator                = Validator::make($category->toArray(), Category::$rules);
            if ($validator->passes()) {
              $category->name = Crypt::encrypt($category->name);
              $category->save();

              $transaction->category_id = $category->id;
            }
          } else {
            $transaction->category_id = $category->id;
          }
        }

        // beneficiary
        if (strlen(Input::get('beneficiary')) != 0) {
          $beneficiaries = Auth::user()->beneficiaries()->get(); //->where('name','=',Input::get('beneficiary'))->first();
          $beneficiary   = null;
          foreach ($beneficiaries as $ben) {
            if (Crypt::decrypt($ben->name) == Input::get('beneficiary')) {
              $beneficiary = $ben;
              break;
            }
          }
          unset($ben, $categories);
          if (is_null($beneficiary)) {

            $beneficiary = new Beneficiary;


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

        if (strlen($transaction->description) > 0) {
          $validator = Validator::make($transaction->toArray(), Transaction::$rules);
          if (!$validator->fails()) {
            $transaction->description = Crypt::encrypt($transaction->description);
            $transaction->save();
          } else {
            echo 'Something failed:<br>';
            var_dump($new);
            echo 'messages:<br>';
            var_dump($validator->fails());
            var_dump($validator->failed());
            echo '<hr />';
            exit();
          }
        }
      }
    }
    return Redirect::to('/home');
  }

  public function newTransaction() {
    $transaction                 = new Transaction;
    $transaction->amount         = floatval(Input::get('amount'));
    $transaction->fireflyuser_id = Auth::user()->id;
    $transaction->date           = Input::get('date');
    $transaction->onetime        = Input::get('onetime') == 'on' ? 1 : 0;
    $transaction->description    = Input:: get('description');

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
    if (strlen(Input::get('category')) != 0) {
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
          $category->name = Crypt::encrypt($category->name);
          $category->save();

          $transaction->category_id = $category->id;
        }
      } else {
        $transaction->category_id = $category->id;
      }
    }


    // beneficiary
    if (strlen(Input::get('beneficiary')) != 0) {
      $beneficiaries = Auth::user()->beneficiaries()->get(); //->where('name','=',Input::get('beneficiary'))->first();
      $beneficiary   = null;
      foreach ($beneficiaries as $ben) {
        if (Crypt::decrypt($ben->name) == Input::get('beneficiary')) {
          $beneficiary = $ben;
          break;
        }
      }
      unset($ben, $categories);
      if (is_null($beneficiary)) {

        $beneficiary = new Beneficiary;


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
      return Redirect::to('/home');
    }
  }

  public function editTransaction($id) {

    $transaction = Auth::user()->transactions()->find($id);
    if ($transaction) {
      $accounts = array();
      foreach (Auth::user()->accounts()->get() as $account) {
        $accounts[$account->id] = Crypt::decrypt($account->name);
      }

      $budgets    = array();
      $budgets[0]
              = '(no budget)';
      foreach (Auth::user()->budgets()->orderBy('date', 'DESC')->take(20)->get() as $budget) {
        $date                 = new Carbon($budget->date);
        $budgets[$budget->id] = Crypt::decrypt($budget->name) . ' (' . $date->format('F Y') . ')';
      }

      $categories = array();
      foreach (Auth::user()->categories()->get() as $cat) {
        $categories[] = Crypt::decrypt($cat->name);
      }

      $beneficiaries = array();
      foreach (Auth::user()->beneficiaries()->get() as $ben) {
        $beneficiaries[] = Crypt::decrypt($ben->name);
      }

      return View::make('transactions.edit')->with('transaction', $transaction)->with('accounts', $accounts)->with('budgets', $budgets)
                      ->with('categories', $categories)
                      ->with('beneficiaries', $beneficiaries);
    } else {
      return App::abort(404);
    }
  }

  public function doEditTransaction($id) {
    $transaction = Auth::user()->transactions()->find($id);
    if ($transaction) {
      // set some basic values:
      $transaction->amount      = floatval(Input::get('amount'));
      $transaction->date        = Input::get('date');
      $transaction->onetime     = Input::get('onetime') == 'on' ? 1 : 0;
      $transaction->description = Input::get('description');

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

      if (strlen(Input::get('category')) != 0) {
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
            $category->name = Crypt::encrypt($category->name);
            $category->save();

            $transaction->category_id = $category->id;
          }
        } else {
          $transaction->category_id = $category->id;
        }
      } else {
        $transaction->category_id = null;
      }


      // beneficiary

      if (strlen(Input::get('beneficiary')) != 0) {
        $beneficiaries = Auth::user()->beneficiaries()->get(); //->where('name','=',Input::get('beneficiary'))->first();
        $beneficiary   = null;
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
          $validator                   = Validator::make(
                          $beneficiary->toArray(), Beneficiary::$rules);
          if ($validator->passes()) {
            $beneficiary->name           = Crypt::encrypt($beneficiary->name);
            $beneficiary->save();
            $transaction->beneficiary_id = $beneficiary->id;
          }
        } else {
          $transaction->beneficiary_id = $beneficiary->id;
        }
      } else {
        $transaction->beneficiary_id = null;
      }

      $validator                = Validator::make($transaction->toArray(), Transaction::$rules);
      $transaction->description = Crypt::encrypt($transaction->description);

      if ($validator->fails()) {
        return Redirect::to('/home/transaction/edit/' . $transaction->id)->withErrors($validator)->withInput();
      } else {
        $transaction->save();
        return Redirect::to('/home/transactions');
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteTransaction($id) {
    $tr = Auth::user()->transactions()->find($id);
    if ($tr) {
      $tr->delete();
      return Redirect::to('/home');
    } else {
      return App::abort(404);
    }
  }

}