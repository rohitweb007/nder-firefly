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
Route::get('/home/import', 'ImportController@showImport');
Route::post('/home/import', 'ImportController@doImport');
Route::get('/concept', 'HomeController@showConcept');
Route::get('/home/logout', 'HomeController@doLogout');
Route::get('/home/delete', 'HomeController@askDelete');
Route::post('/home/delete', 'HomeController@doDelete');
Route::get('/home', 'HomeController@getHome');

Route::get('/home/settings', 'SettingsController@settings');
Route::post('/home/settings', 'SettingsController@save');
Route::post('/home/settings/update', 'SettingsController@update');
Route::post('/home/settings/add', 'SettingsController@addSetting');
Route::post('/home/settings/delete', 'SettingsController@deleteSetting');
Route::get('/home/amounts', array('as'   => 'amounts', 'uses' => 'SettingsController@amounts'));

# compare things:
Route::get('/home/compare/basictable', 'ComparisionController@basicTable');
Route::get('/home/compare/basicchart', 'ComparisionController@basicChart');
Route::get('/home/compare/categories', 'ComparisionController@compareCategories');
Route::get('/home/compare/budgets', 'ComparisionController@compareBudgets');
# charts
Route::get('/home/chart/ovcat', 'ChartController@showOverExpendingCategories');
Route::get('/home/chart/predict', 'ChartController@predictionChart');
Route::get('/home/chart/bba/{id}', 'AccountController@showBudgetsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/cba/{id}', 'AccountController@showCategoriesInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/mba/{id}', 'AccountController@showMovesInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/benba/{id}',   'AccountController@showBeneficiariesInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/transba/{id}', 'AccountController@showTransactionsInTimeframe')->where('id', '[0-9]+');

Route::get('/home/charts/prediction', 'PageController@predictionChart');
Route::get('/home/charts/compare', 'PageController@compare');


// account management:
Route::get('/home/account/add', 'AccountController@addAccount');
Route::post('/home/account/add', 'AccountController@newAccount');
Route::get('/home/accounts', 'AccountController@showAll');
Route::get('/home/accounts/chart', 'AccountController@showAllChart');
Route::get('/home/account/overview/{id}', 'AccountController@showAccountOverview')->where('id', '[0-9]+');
Route::get('/home/account/overviewGraph/{id}', 'AccountController@homeOverviewGraph')->where('id', '[0-9]+');
Route::get('/home/account/chart/{id}', 'AccountController@overviewGraph')->where('id', '[0-9]+');
Route::post('/home/account/delete/{id}', 'AccountController@deleteAccount')->where('id', '[0-9]+');
Route::get('/home/account/edit/{id}', 'AccountController@editAccount')->where('id', '[0-9]+');
Route::post('/home/account/edit/{id}', 'AccountController@doEditAccount')->where('id', '[0-9]+');
Route::get('/home/account/summary/{id}', 'AccountController@getAccountSummary')->where('id', '[0-9]+');

// beneficiary management
Route::get('/home/beneficiaries', 'BeneficiaryController@showAll');
Route::get('/home/beneficiary/overview/{id}', 'BeneficiaryController@showOverview')->where('id', '[0-9]+');
Route::get('/home/beneficiary/edit/{id}', 'BeneficiaryController@editBeneficiary')->where('id', '[0-9]+');
Route::post('/home/beneficiary/edit/{id}', 'BeneficiaryController@doEditBeneficiary')->where('id', '[0-9]+');
Route::post('/home/beneficiary/delete/{id}', 'BeneficiaryController@deleteBeneficiary')->where('id', '[0-9]+');
Route::get('/home/beneficiary/chart/{id}', 'BeneficiaryController@overviewGraph')->where('id', '[0-9]+');


// budget management
Route::get('/home/budgets', 'BudgetController@showAll');
Route::get('/home/budget/add', 'BudgetController@addBudget');
Route::get('/home/budget/edit/{id}', 'BudgetController@editBudget');
Route::post('/home/budget/edit/{id}', 'BudgetController@doEditBudget');
Route::post('/home/budget/add', 'BudgetController@newBudget');
Route::get('/home/budget/overview/{id}', 'BudgetController@showBudgetOverview')->where('id', '[0-9]+');
Route::get('/home/budget/overviewGraph/{id}', 'BudgetController@homeOverviewGraph')->where('id', '[0-9]+');
Route::post('/home/budget/delete/{id}', 'BudgetController@deleteBudget')->where('id', '[0-9]+');

// transaction management
Route::get('/home/transactions', 'TransactionController@showAll');
Route::get('/home/transaction/add', 'TransactionController@addTransaction');
Route::post('/home/transaction/add', 'TransactionController@newTransaction');
Route::get('/home/transaction/edit/{id}', 'TransactionController@editTransaction')->where('id', '[0-9]+');
Route::post('/home/transaction/edit/{id}', 'TransactionController@doEditTransaction')->where('id', '[0-9]+');
Route::post('/home/transaction/delete/{id}', 'TransactionController@deleteTransaction')->where('id', '[0-9]+');

// target management
Route::get('/home/targets', 'TargetController@showAll');
Route::get('/home/target/add', 'TargetController@addTarget');
Route::post('/home/target/add', 'TargetController@newTarget');
Route::get('/home/target/edit/{id}', 'TargetController@editTarget');
Route::post('/home/target/edit/{id}', 'TargetController@doEditTarget');
Route::get('/home/target/overviewGraph/{id}', 'TargetController@homeOverviewGraph')->where('id', '[0-9]+');
Route::post('/home/target/delete/{id}', 'TargetController@deleteTarget')->where('id', '[0-9]+');

// transfer management
Route::get('/home/transfers', 'TransferController@showAll');
Route::get('/home/transfer/add', 'TransferController@addTransfer');
Route::post('/home/transfer/add', 'TransferController@newTransfer');
Route::get('/home/transfer/edit/{id}', 'TransferController@editTransfer')->where('id', '[0-9]+');
Route::post('/home/transfer/edit/{id}', 'TransferController@doEditTransfer')->where('id', '[0-9]+');
Route::post('/home/transfer/delete/{id}', 'TransferController@deleteTransfer')->where('id', '[0-9]+');


// category list:
Route::get('/home/categories', 'CategoryController@showAll');
Route::get('/home/categories/{id}', 'CategoryController@showSingle');
Route::get('/home/category/edit/{id}', 'CategoryController@editCategory')->where('id', '[0-9]+');
Route::post('/home/category/edit/{id}', 'CategoryController@doEditCategory')->where('id', '[0-9]+');
Route::post('/home/category/delete/{id}', 'CategoryController@deleteCategory')->where('id', '[0-9]+');



Route::get('/home/{year?}/{month?}', 'HomeController@getHome');