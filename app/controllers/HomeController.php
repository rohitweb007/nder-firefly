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
    $key = cacheKey('home', Session::get('period'));
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $max  = 0;
      $min  = 1000000;
      $data = array(
          'accounts' => array(),
          'budgets'  => array(),
          'targets'  => array()
      );

      $accounts = Auth::user()->accounts()->get();
      foreach ($accounts as $a) {
        $account           = array(
            'id'             => intval($a->id),
            'name'           => Crypt::decrypt($a->name),
            'currentbalance' => $a->balance()
        );
        $account['header'] = $account['currentbalance'] < 0 ? array('style' => 'color:red;', 'class' => 'tt', 'title' => $account['name'] . ' has a balance below zero. Try to fix this.') : array();

        $min = $account['currentbalance'] < $min ? $account['currentbalance'] : $min;
        $max = $account['currentbalance'] > $max ? $account['currentbalance'] : $max;

// last transactions and transfers for this account:
        $list = array();
        foreach ($a->transactions()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date             = $t->date;
          $list[$date]      = isset($list[$date]) ? $list[$date] : array();
          $t->description   = Crypt::decrypt($t->description);
          $t->date          = new DateTime($t->date);
          $t->category_name = is_null($t->category_id) ? null : Crypt::decrypt($t->category()->first()->name);
          $t->type          = 'Transaction';
          $list[$date][]    = $t->toArray();
        }
        foreach ($a->transfersto()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date                 = $t->date;
          $list[$date]          = isset($list[$date]) ? $list[$date] : array();
          $t->date              = new DateTime($t->date);
          $t->description       = Crypt::decrypt($t->description);
          $t->category_name     = is_null($t->category_id) ? null : Crypt::decrypt($t->category()->first()->name);
          $t->account_to_name   = Crypt::decrypt($t->accountto()->first()->name);
          $t->account_from_name = Crypt::decrypt($t->accountfrom()->first()->name);
          $t->type              = 'Transfer';
          $list[$date][]        = $t->toArray();
        }
        foreach ($a->transfersfrom()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date                 = $t->date;
          $list[$date]          = isset($list[$date]) ? $list[$date] : array();
          $t->description       = Crypt::decrypt($t->description);
          $t->date              = new DateTime($t->date);
          $t->category_name     = is_null($t->category_id) ? null : Crypt::decrypt($t->category()->first()->name);
          $t->account_to_name   = Crypt::decrypt($t->accountto()->first()->name);
          $t->account_from_name = Crypt::decrypt($t->accountfrom()->first()->name);
          $t->type              = 'Transfer';
          $list[$date][]        = $t->toArray();
        }
        krsort($list);
        $account['list']    = $list;
        $data['accounts'][] = $account;
      }

      $min = $min > 0 ? 0 : $min;
      $max = $max < 0 ? 0 : $max;
      $min = floor($min / 1000) * 1000;
      $max = ceil($max / 1000) * 1000;
      $sum = 0;
      foreach ($data['accounts'] as $index => $account) {
        $maxpct                             = $max != 0 ? ceil(($account['currentbalance'] / $max) * 100) : 0;
        $data['accounts'][$index]['maxpct'] = $maxpct < 0 ? 0 : $maxpct;
        $minpct                             = $min != 0 ? floor(($account['currentbalance'] / $min) * 100) : 0;
        $data['accounts'][$index]['minpct'] = $minpct < 0 ? 0 : $minpct;
        $sum+= $account['currentbalance'];
      }
      $data['acc_data']['sum'] = $sum;


// now everything for budgets:
      $budgets = Auth::user()->budgets()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->get();
      foreach ($budgets as $b) {
        $budget             = array(
            'id'       => intval($b->id),
            'name'     => Crypt::decrypt($b->name),
            'widthpct' => $b->amount > 0 ? ceil(($b->spent() / $b->amount) * 100) : 100,
            'expected' => $b->expected(),
            'spent'    => $b->spent(),
            'left'     => $b->left(),
            'advice'   => $b->advice(),
            'amount'   => floatval($b->amount),
        );
        $budget['overflow'] = $budget['expected'] > $b->left();

        $list = array();
        foreach ($b->transactions()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date             = $t->date;
          $t->description   = Crypt::decrypt($t->description);
          $list[$date]      = isset($list[$date]) ? $list[$date] : array();
          $t->date          = new DateTime($t->date);
          $t->type          = 'Transaction';
          $t->category_name = is_null($t->category_id) ? null : Crypt::decrypt($t->category()->first()->name);
          $list[$date][]    = $t->toArray();
        }
        foreach ($b->transfers()->take(5)->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get() as $t) {
          $date                 = $t->date;
          $t->description       = Crypt::decrypt($t->description);
          $list[$date]          = isset($list[$date]) ? $list[$date] : array();
          $t->date              = new DateTime($t->date);
          $t->type              = 'Transfer';
          $t->account_to_name   = Crypt::decrypt($t->accountto()->first()->name);
          $t->account_from_name = Crypt::decrypt($t->accountfrom()->first()->name);
          $t->category_name     = is_null($t->category_id) ? null : Crypt::decrypt($t->category()->first()->name);
          $list[$date][]        = $t->toArray();
        }
        $budget['list']    = $list;
        $data['budgets'][] = $budget;
      }

      // targets
      $db = Auth::user()->targets()->orderBy('duedate', 'DESC')->get();
      foreach ($db as $t) {
        $tr                = array(
            'id'          => $t->id,
            'description' => Crypt::decrypt($t->description),
            'amount'      => floatval($t->amount),
            'duedate'     => $t->duedate != '0000-00-00' ? new DateTime($t->duedate) : null,
            'startdate'   => $t->startdate != '0000-00-00' ? new DateTime($t->startdate) : null,
            'saved'       => $t->hassaved(),
            'guide'       => $t->guide(),
            'should'      => $t->shouldhavesaved()
        );
        $tr['pct']         = round(($tr['saved'] / $tr['amount']) * 100, 2);
        $data['targets'][] = $tr;
      }
      Cache::put($key, $data, 2440);
    }
    return View::make('home.home')->with('data', $data);
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