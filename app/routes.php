<?php

require_once 'google/appengine/api/users/User.php';
require_once 'google/appengine/api/users/UserService.php';

use google\appengine\api\users\User;
use google\appengine\api\users\UserService;

Route::get('/', 'HomeController@getRoot');
Route::get('/concept', 'HomeController@showConcept');
Route::get('/home', 'HomeController@getHome');
Route::get('/home/delete', 'HomeController@askDelete');
Route::get('/home/export', 'ImportController@doExport');
Route::get('/home/flush', 'HomeController@doFlush');
Route::get('/home/import', 'ImportController@showImport');
Route::get('/home/logout', 'HomeController@doLogout');
Route::get('/home/settings', 'SettingsController@settings');
Route::get('/home/amounts','SettingsController@amounts');
Route::post('/home/settings', 'SettingsController@save');
Route::post('/home/settings/update', 'SettingsController@update');
Route::post('/home/settings/add', 'SettingsController@addSetting');
Route::post('/home/settings/delete', 'SettingsController@deleteSetting');
Route::post('/home/import', 'ImportController@doImport');
Route::post('/home/delete', 'HomeController@doDelete');

# compare things:
Route::get('/home/compare/basictable', 'ComparisionController@basicTable');
Route::get('/home/compare/basicchart', 'ComparisionController@basicChart');
Route::get('/home/compare/categories', 'ComparisionController@compareCategories');
Route::get('/home/compare/budgets', 'ComparisionController@compareBudgets');

# pages for charts
Route::get('/home/charts/prediction', 'PageController@predictionChart');
Route::get('/home/charts/compare', 'PageController@compare');

# charts themselves
Route::get('/home/chart/ovcat', 'ChartController@showOverExpendingCategories');
Route::get('/home/chart/predict', 'ChartController@predictionChart');
Route::get('/home/chart/bba/{id}', 'AccountController@showBudgetsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/cba/{id}', 'AccountController@showCategoriesInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/mba/{id}', 'AccountController@showMovesInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/benba/{id}', 'AccountController@showBeneficiariesInTimeframe')->where('id', '[0-9]+');
Route::get('/home/chart/transba/{id}', 'AccountController@showTransactionsInTimeframe')->where('id', '[0-9]+');



# account management:
Route::get('/home/account/add', 'AccountController@addAccount');
Route::get('/home/accounts', 'AccountController@showAll');
Route::get('/home/accounts/chart', 'AccountController@showAllChart');
Route::get('/home/account/overview/{id}', 'AccountController@showAccountOverview')->where('id', '[0-9]+');
Route::get('/home/account/overviewGraph/{id}', 'AccountController@homeOverviewGraph')->where('id', '[0-9]+');
Route::get('/home/account/chart/{id}', 'AccountController@overviewGraph')->where('id', '[0-9]+');
Route::get('/home/account/edit/{id}', 'AccountController@editAccount')->where('id', '[0-9]+');
Route::get('/home/account/summary/{id}', 'AccountController@getAccountSummary')->where('id', '[0-9]+');
Route::post('/home/account/add', 'AccountController@newAccount');
Route::post('/home/account/delete/{id}', 'AccountController@deleteAccount')->where('id', '[0-9]+');
Route::post('/home/account/edit/{id}', 'AccountController@doEditAccount')->where('id', '[0-9]+');

# beneficiary management
Route::get('/home/beneficiaries', 'BeneficiaryController@showAll');
Route::get('/home/beneficiary/overview/{id}', 'BeneficiaryController@showOverview')->where('id', '[0-9]+');
Route::get('/home/beneficiary/edit/{id}', 'BeneficiaryController@editBeneficiary')->where('id', '[0-9]+');
Route::get('/home/beneficiary/chart/{id}', 'BeneficiaryController@overviewGraph')->where('id', '[0-9]+');
Route::get('/home/beneficiary/summary/{id}', 'BeneficiaryController@getBeneficiarySummary')->where('id', '[0-9]+');
Route::get('/home/beneficiary/transactions/{id}', 'BeneficiaryController@showTransactionsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/beneficiary/budgets/{id}', 'BeneficiaryController@showBudgetsInTimeframe')->where('id', '[0-9]+');
Route::post('/home/beneficiary/edit/{id}', 'BeneficiaryController@doEditBeneficiary')->where('id', '[0-9]+');
Route::post('/home/beneficiary/delete/{id}', 'BeneficiaryController@deleteBeneficiary')->where('id', '[0-9]+');


# budget management
Route::get('/home/budgets', 'BudgetController@showAll');
Route::get('/home/budget/add', 'BudgetController@addBudget');
Route::get('/home/budget/edit/{id}', 'BudgetController@editBudget');
Route::get('/home/budget/overview/{id}', 'BudgetController@showBudgetOverview')->where('id', '[0-9]+');
Route::get('/home/budget/overviewGraph/{id}', 'BudgetController@homeOverviewGraph')->where('id', '[0-9]+');
Route::post('/home/budget/edit/{id}', 'BudgetController@doEditBudget');
Route::post('/home/budget/add', 'BudgetController@newBudget');
Route::post('/home/budget/delete/{id}', 'BudgetController@deleteBudget')->where('id', '[0-9]+');

# transaction management
Route::get('/home/transactions', 'TransactionController@showAll');
Route::get('/home/transaction/add', 'TransactionController@addTransaction');
Route::get('/home/transaction/add/mass', 'TransactionController@massAddTransaction');
Route::get('/home/transaction/edit/{id}', 'TransactionController@editTransaction')->where('id', '[0-9]+');
Route::post('/home/transaction/add/mass', 'TransactionController@massNewTransaction');
Route::post('/home/transaction/add', 'TransactionController@newTransaction');
Route::post('/home/transaction/edit/{id}', 'TransactionController@doEditTransaction')->where('id', '[0-9]+');
Route::post('/home/transaction/delete/{id}', 'TransactionController@deleteTransaction')->where('id', '[0-9]+');

# target management
Route::get('/home/targets', 'TargetController@showAll');
Route::get('/home/target/add', 'TargetController@addTarget');
Route::get('/home/target/edit/{id}', 'TargetController@editTarget');
Route::get('/home/target/overviewGraph/{id}', 'TargetController@homeOverviewGraph')->where('id', '[0-9]+');
Route::post('/home/target/add', 'TargetController@newTarget');
Route::post('/home/target/edit/{id}', 'TargetController@doEditTarget');
Route::post('/home/target/delete/{id}', 'TargetController@deleteTarget')->where('id', '[0-9]+');

# transfer management
Route::get('/home/transfers', 'TransferController@showAll');
Route::get('/home/transfer/add', 'TransferController@addTransfer');
Route::get('/home/transfer/edit/{id}', 'TransferController@editTransfer')->where('id', '[0-9]+');
Route::post('/home/transfer/add', 'TransferController@newTransfer');
Route::post('/home/transfer/edit/{id}', 'TransferController@doEditTransfer')->where('id', '[0-9]+');
Route::post('/home/transfer/delete/{id}', 'TransferController@deleteTransfer')->where('id', '[0-9]+');


# category list:
Route::get('/home/categories', 'CategoryController@showAll');
Route::get('/home/categories/{id}', 'CategoryController@showSingle');
Route::get('/home/category/edit/{id}', 'CategoryController@editCategory')->where('id', '[0-9]+');
Route::post('/home/category/edit/{id}', 'CategoryController@doEditCategory')->where('id', '[0-9]+');
Route::post('/home/category/delete/{id}', 'CategoryController@deleteCategory')->where('id', '[0-9]+');


# home with a specific date.
Route::get('/home/{year?}/{month?}', 'HomeController@getHome');