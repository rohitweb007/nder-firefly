<?php

require_once 'google/appengine/api/users/User.php';
require_once 'google/appengine/api/users/UserService.php';
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;

Route::get('/', function() {
          $user = UserService::getCurrentUser();

          if (isset($user)) {
            return Redirect::to('/home');
          } else {
            $url = UserService::createLoginUrl('/home');
            return View::make('home.index')->with('url', $url);
          }
        });

Route::get('/oldimport', 'ImportController@doOldImport');
Route::get('/home/flush', function() {
          Cache::flush();
          return Redirect::to('/home');
        });
Route::get('/home/export', 'ImportController@doExport');
Route::get('/concept', 'HomeController@showConcept');
Route::get('/home/logout', 'HomeController@doLogout');
Route::get('/home/delete', 'HomeController@askDelete');
Route::post('/home/delete', 'HomeController@doDelete');
Route::get('/home', 'HomeController@getHome');

# charts
Route::get('/home/chart/ovcat', 'ChartController@showOverExpendingCategories');
Route::get('/home/chart/bba/{id}', 'ChartController@showBudgetsByAccount')->where('id', '[0-9]+');
Route::get('/home/chart/cba/{id}', 'ChartController@showCategoriesByAccount')->where('id', '[0-9]+');
Route::get('/home/chart/tba/{id}', 'ChartController@showTransfersByAccount')->where('id', '[0-9]+');


// account management:
route::get('/home/account/add', 'AccountController@addAccount');
route::post('/home/account/add', 'AccountController@newAccount');
route::get('/home/account/overview/{id}', 'AccountController@showAccountOverview')->where('id', '[0-9]+');
route::get('/home/account/overviewGraph/{id}', 'AccountController@homeOverviewGraph')->where('id', '[0-9]+');
route::get('/home/account/chart/{id}', 'AccountController@overviewGraph')->where('id', '[0-9]+');

// budget management
route::get('/home/budget/add', 'BudgetController@addBudget');
route::post('/home/budget/add', 'BudgetController@newBudget');
route::get('/home/budget/overview/{id}', 'BudgetController@showBudgetOverview')->where('id', '[0-9]+');
route::get('/home/budget/overviewGraph/{id}', 'BudgetController@homeOverviewGraph')->where('id', '[0-9]+');

// transaction management
route::get('/home/transactions', 'TransactionController@showAll');
route::get('/home/transaction/add', 'TransactionController@addTransaction');
route::post('/home/transaction/add', 'TransactionController@newTransaction');

// target management
route::get('/home/target/add', 'TargetController@addTarget');
route::post('/home/target/add', 'TargetController@newTarget');
route::get('/home/target/overviewGraph/{id}', 'TargetController@homeOverviewGraph')->where('id', '[0-9]+');


// transfer management
route::get('/home/transfers', 'TransferController@showAll');
route::get('/home/transfer/add', 'TransferController@addTransfer');
route::post('/home/transfer/add', 'TransferController@newTransfer');

// category list:
route::get('/home/categories', 'CategoryController@showAll');
route::get('/home/categories/{id}', 'CategoryController@showSingle');

Route::get('/home/{year?}/{month?}', 'HomeController@getHome');