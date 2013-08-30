<?php

use Carbon\Carbon as Carbon;

class CategoryController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    $key = cacheKey('Categories', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data       = array();
      $categories = Auth::user()->categories()->orderBy('id', 'ASC')->get();
      // to get the avg per month we first need the number of months

      foreach ($categories as $cat) {
        $name = Crypt::decrypt($cat->name);
        $cate = array(
            'id'   => intval($cat->id),
            'name' => $name,
        );

        $now           = new Carbon('now');
        $thisMonth     = $cat->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $now->format('m-Y'))->sum('amount');
        $cate['month'] = floatval($thisMonth);

        $data[] = $cate;
      }
      unset($name);$name=array();
      // order by alfabet
      // Obtain a list of columns
      foreach ($data as $key => $row) {
        $id[$key]  = $row['id'];
        $name[$key] = $row['name'];
      }
      array_multisort($name, SORT_ASC, $id, SORT_DESC, $data);
      Cache::put($key, $data, 1440);
    }
    return View::make('categories.all')->with('categories', $data);
  }

  public function editCategory($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      return View::make('categories.edit')->with('category', $category);
    } else {
      return App::abort(404);
    }
  }

  public function doEditCategory($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $category->name = Input::get('name');
      $validator      = Validator::make($category->toArray(), Category::$rules);
      if ($validator->fails()) {
        Log::error('Could not edit category for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($category, true));
        return Redirect::to('/home/category/edit/' . $category->id)->withErrors($validator)->withInput();
      } else {
        $category->name = Crypt::encrypt($category->name);
        $category->save();
        return Redirect::to('/home/categories');
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteCategory($id) {

    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $category->delete();
      return Redirect::to('/home/categories');
    } else {
      return App::abort(404);
    }
  }

  public function overSpendingByName($name) {
    // find the category:
    $categories = Auth::user()->categories()->get();
    foreach ($categories as $cat) {
      if (Crypt::decrypt($cat->name) == $name) {
        return Redirect::to('/home/category/overspending/' . $cat->id);
      }
    }
  }

  public function overSpending($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $key = cacheKey('overspending', $id);
      if (Cache::has($key)) {
        $data = Cache::get($key);
      } else {
        $data = array();
        $data['sum'] = 0;
        $period               = Session::get('period');
        // let's collect some intel.
        // first: transactions in this category.
        $trans                = $category->transactions()->
                        leftJoin('accounts', 'accounts.id', '=', 'account_id')->
                        leftJoin('budgets', 'budgets.id', '=', 'budget_id')->
                        leftJoin('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_id')->
                        where(DB::Raw('DATE_FORMAT(`transactions`.`date`,"%m-%Y")'), '=', $period->format('m-Y'))->
                        //where('onetime','=',0)->
                        orderBy('transactions.date', 'DESC')->get(array(
            'transactions.id','transactions.date',
            'account_id', 'accounts.name AS account_name',
            'budget_id', 'budgets.name AS budget_name',
            'beneficiary_id', 'beneficiaries.name AS beneficiary_name',
            'transactions.date', 'description', 'transactions.amount', 'onetime'
        ));
        $data['transactions'] = array();
        foreach ($trans as $t) {
          $tr                     = array(
              'id'               => $t->id,
              'date'             => new Carbon($t->date),
              'account_id'       => $t->account_id,
              'account_name'     => Crypt::decrypt($t->account_name),
              'budget_id'        => $t->budget_id,
              'budget_name'      => is_null($t->budget_name) ? null : Crypt::decrypt($t->budget_name),
              'beneficiary_id'   => $t->beneficiary_id,
              'beneficiary_name' => is_null($t->beneficiary_name) ? null : Crypt::decrypt($t->beneficiary_name),
              'description'      => Crypt::decrypt($t->description),
              'amount'           => floatval($t->amount) * -1,
              'onetime'          => $t->onetime == 1 ? true : false,
          );
          if(!$tr['onetime']) {
            $data['sum'] += floatval($t->amount) *-1;
          }
          $data['transactions'][] = $tr;
        }
        // TODO REMOVE THIS.
        
        // van elke vorige maand de average.
        // dat getal moet overeen komen met de average uit de grafiek!
        $first        = BaseController::getFirst();
        $first->day   = intval($period->format('d'));
        $last         = BaseController::getLast();
        $last->day    = intval($period->format('d'));
        $data['past'] = array();
        $sum          = 0;

        // get spending so far for each month:
        while ($first <= $last) {
          $spent          = $category->spent($first);
          $arr            = array(
              'date'  => $first->format('F Y'),
              'spent' => $spent,
              'start_date' => $first->format('Y-m-').'01',
              'end_date' => $first->format('Y-m-t'),
          );
          $data['past'][] = $arr;
          if ($first != $last) {
            $sum+=$arr['spent'];
          }

          $first->addMonth();
        }
        $data['average'] = $category->averagespending();



        return View::make('categories.overspending')->with('category', $category)->with('data', $data);
      }
    }
  }

}