<?php

require_once 'google/appengine/api/users/User.php';
require_once 'google/appengine/api/users/UserService.php';

use google\appengine\api\users\UserService;
use Holmes\Holmes;
use Carbon\Carbon as Carbon;
class HomeController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs', array('only' => 'getHome', 'askDelete', 'doDelete', 'doLogout')); // do Google "sync".
  }

  /**
   * The very very first page.
   */
  public function getRoot() {
    $user = UserService::getCurrentUser();

    if (isset($user)) {
      return Redirect::to('/home');
    } else {
      $url = UserService::createLoginUrl('/home');
      return View::make('home.index')->with('url', $url);
    }
  }

  public function doFlush() {
    return Redirect::to('/home');
  }

  public function getHome() {
    $event = Event::fire('firefly.home',null);

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
      // we need this list:
      $accounts = Auth::user()->accounts()->get();

      foreach ($accounts as $a) {
        $account            = array(
            'id'             => intval($a->id),
            'name'           => Crypt::decrypt($a->name),
            // we need this query to be sure to be up-to-date:
            'currentbalance' => $a->balance()
        );


        $account['header']  = $account['currentbalance'] < 0 ? array('style' => 'color:red;', 'class' => 'tt', 'title' => $account['name'] . ' has a balance below zero. Try to fix this.') : array();
        $min                = $account['currentbalance'] < $min ? $account['currentbalance'] : $min;
        $max                = $account['currentbalance'] > $max ? $account['currentbalance'] : $max;
        $data['accounts'][] = $account;
      }

      $min = $min > 0 ? 0 : $min;
      $max = $max < 0 ? 0 : $max;
      $min = floor($min / 1000) * 1000;
      $max = ceil($max / 1000) * 1000;
      $sum = 0;
      foreach ($data['accounts'] as $index => $account) {
        $sum+= $account['currentbalance'];
      }
      $data['acc_data']['sum'] = $sum;



      // now everything for budgets:
      $data['budgets'] = Budget::getHomeOverview();

      // some extra budget data:
      $monthlyAmount = Setting::getSetting('monthlyAmount', Session::get('period')->format('Y-m-') . '01');

      if (is_null($monthlyAmount)) {
        $monthlyAmount = intval(Setting::getSetting('defaultAmount'));
      }

      $data['budget_data']['amount']        = $monthlyAmount;
      $data['budget_data']['spent_outside'] = floatval(Auth::user()->transactions()->where('amount', '<', 0)->whereNull('budget_id')->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->sum('amount')) * -1;
      $data['budget_data']['spent_outside'] += floatval(Auth::user()->transfers()->where('countasexpense', '=', 1)->whereNull('budget_id')->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', Session::get('period')->format('m-Y'))->sum('amount'));


      // targets, cant make it better im afraid.
      $data['targets'] = Target::getHomeOverview();
      Cache::put($key, $data, 2440);
    }
    // flash some warnings:
    if (count($data['budgets']) == 0) {
      Session::flash('warning', 'You don\'t have any budgets defined.');
    }
    if (count($data['accounts']) == 0) {
      Session::flash('warning', 'You do not have any accounts added. You should do this first (Create &rarr; New account)');
    }


    if (Holmes::isMobile()) {
      return View::make('mobile.home.home')->with('data', $data);
    } else {
      return View::make('home.home')->with('data', $data);
    }
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