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
        $cate = array(
            'id'   => intval($cat->id),
            'name' => Crypt::decrypt($cat->name),
        );

        $now           = new Carbon('now');
        $thisMonth     = $cat->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $now->format('m-Y'))->sum('amount');
        $cate['month'] = floatval($thisMonth);

        $data[] = $cate;
      }
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

        // then, transfers in this category:
        $transf            = $category->transfers()->
                        leftJoin('accounts as af', 'af.id', '=', 'account_from')->
                        leftJoin('accounts as at', 'at.id', '=', 'account_to')->
                        leftJoin('budgets', 'budgets.id', '=', 'budget_id')->
                        leftJoin('targets', 'targets.id', '=', 'target_id')->
                        where(DB::Raw('DATE_FORMAT(`transfers`.`date`,"%m-%Y")'), '=', $period->format('m-Y'))->
                        orderBy('transfers.date', 'DESC')->orderBy('transfers.created_at', 'DESC')->get(
                array(
                    'transfers.id',
                    'account_to', 'at.name AS account_to_name',
                    'account_from', 'af.name AS account_from_name',
                    'budget_id', 'budgets.name AS budget_name',
                    'target_id', 'targets.description AS target_description',
                    'transfers.date', 'transfers.description', 'transfers.amount', 'countasexpense', 'ignoreprediction'
                )
        );
        $data['transfers'] = array();
        foreach ($transf as $t) {
          $current             = array(
              'id'                 => intval($t->id),
              'description'        => Crypt::decrypt($t->description),
              'amount'             => mf(floatval($t->amount)),
              'date'             => new Carbon($t->date),
              'account_to'         => $t->account_to,
              'account_to_name'    => Crypt::decrypt($t->account_to_name),
              'account_from'       => $t->account_from,
              'account_from_name'  => Crypt::decrypt($t->account_from_name),
              'budget_id'          => $t->budget_id,
              'budget_name'        => (is_null($t->budget_id) ? null : Crypt::decrypt($t->budget_name)),
              'target_id'          => $t->target_id,
              'target_description' => (is_null($t->target_id) ? null : Crypt::decrypt($t->target_description)),
              'ignoreprediction'   => $t->ignoreprediction == 1 ? true : false,
              'countasexpense'     => $t->countasexpense == 1 ? true : false,
          );
          if($t['countasexpense']) {
            $data['sum'] += floatval($t->amount);
          }
          $data['transfers'][] = $current;
        }
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
              'spent' => $spent
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