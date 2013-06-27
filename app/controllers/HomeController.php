<?php

require_once 'google/appengine/api/users/User.php';
require_once 'google/appengine/api/users/UserService.php';

use google\appengine\api\users\User;
use google\appengine\api\users\UserService;

class HomeController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs', array('only' => 'getHome')); // do Google "sync".
  }

  public function getHome() {
    



    $max = 0;
    $min = 1000000;

    $accountsKey = cacheKey('getHome', 'accounts');
    $budgetsKey  = cacheKey('getHome', 'budgets');
    $targetKey = cacheKey('getHome','targets');
    Cache::has($accountsKey);
    if (Cache::has($accountsKey)) {
      $accounts = Cache::get($accountsKey);

    } else {
      $accounts = Auth::user()->accounts()->get();

      foreach ($accounts as $account) {
        $account->name           = Crypt::decrypt($account->name);
        $account->currentbalance = $account->balance();
        $min                     = $account->currentbalance < $min ? $account->currentbalance : $min;
        $max                     = $account->currentbalance > $max ? $account->currentbalance : $max;

        // get last five transactions and transfers.
        $list = array();
        foreach ($account->transactions()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date          = $t->date;
          $list[$date]   = isset($list[$date]) ? $list[$date] : array();
          $list[$date][] = $t;
        }
        foreach ($account->transfersto()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date          = $t->date;
          $list[$date]   = isset($list[$date]) ? $list[$date] : array();
          $list[$date][] = $t;
        }
        foreach ($account->transfersfrom()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date          = $t->date;
          $list[$date]   = isset($list[$date]) ? $list[$date] : array();
          $list[$date][] = $t;
        }
        krsort($list);
        $account->list = $list;
      }
      // order the list.



      $min = $min > 0 ? 0 : $min;
      $max = $max < 0 ? 0 : $max;
      // we make steps of a 1000 euro's.
      $min = floor($min / 1000) * 1000;
      $max = ceil($max / 1000) * 1000;
      foreach ($accounts as $account) {
        $maxpct          = $max != 0 ? ceil(($account->currentbalance / $max) * 100) : 0;
        $account->maxpct = $maxpct < 0 ? 0 : $maxpct;
        $minpct          = $min != 0 ? floor(($account->currentbalance / $min) * 100) : 0;
        $account->minpct = $minpct < 0 ? 0 : $minpct;
      }
      Cache::put($accountsKey, $accounts, 1440);
    }

    // budgets:
    if (Cache::has($budgetsKey)) {
      $budgets = Cache::get($budgetsKey);
    } else {
      $budgets = Auth::user()->budgets()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->get();
      foreach ($budgets as $budget) {
        $budget->name     = Crypt::decrypt($budget->name);
        $budget->widthpct = $budget->amount > 0 ? ceil(($budget->spent() / $budget->amount) * 100) : 100;
        $budget->expected = $budget->expected();
        $budget->overflow = $budget->expected > $budget->left();
        $budget->amount = floatval($budget->amount);
        // get last five transactions and transfers:
        $list = array();
        foreach ($budget->transactions()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date          = $t->date;
          $list[$date]   = isset($list[$date]) ? $list[$date] : array();
          $list[$date][] = $t;
        }
        foreach ($budget->transfers()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date          = $t->date;
          $list[$date]   = isset($list[$date]) ? $list[$date] : array();
          $list[$date][] = $t;
        }
        $budget->list = $list;
      }
      Cache::put($budgetsKey,$budgets,1440);
    }

    // targets
    if(!Cache::has($targetKey) && count($accounts) > 1) {
      $targets = array();
      $db = Auth::user()->targets()->orderBy('duedate','DESC')->get();
      foreach($db as $t) {
        $tr = array(
            'id' => $t->id,
            'description' => Crypt::decrypt($t->description),
            'amount' => floatval($t->amount),
            'duedate' => $t->duedate != '0000-00-00' ? new DateTime($t->duedate) : null,
            'startdate' => $t->startdate != '0000-00-00' ? new DateTime($t->startdate) : null,
            'saved' => $t->hassaved(),
            'guide' => $t->guide(),
            'should' => $t->shouldhavesaved()
        );
        $tr['pct'] = round(($tr['saved'] / $tr['amount']) * 100,2);
        $targets[] = $tr;
      }

      Cache::put($targetKey,$targets,1440);
    } else {
      $targets = Cache::get($targetKey);
    }


    return View::make('home.home')->with('accounts', $accounts)->with('budgets', $budgets)
            ->with('targets',$targets);
  }

  public function getIndex() {
    return View::make('home.index');
  }

  public function doLogout() {
    Auth::logout();
    Session::flush();
    return Redirect::to(UserService::createLogoutUrl('/'));
  }

  public function askDelete() {
    return View::make('home.delete');
  }
  public function doDelete() {
    $user = Auth::user();
    Auth::logout();
    Session::flush();
    $user->delete();
    return Redirect::to(UserService::createLogoutUrl('/'));
  }


  public function showConcept() {
    return View::make('home.concept');
  }

}