<?php
Route::get('/', 'HomeController@getRoot');

Route::get('/concept', 'HomeController@showConcept');
Route::get('/home', 'HomeController@getHome');
Route::get('/home/delete', 'HomeController@askDelete');
Route::get('/home/export', 'ImportController@doExport');
Route::get('/home/flush', 'HomeController@doFlush');
Route::get('/home/import', 'ImportController@showImport');
Route::get('/home/logout', 'HomeController@doLogout');
Route::get('/home/settings', 'SettingsController@settings');
Route::get('/home/amounts',array('as' => 'amounts','uses'  => 'SettingsController@amounts'));
Route::post('/home/settings', 'SettingsController@save');
Route::post('/home/settings/update', 'SettingsController@update');
Route::post('/home/settings/add', 'SettingsController@addSetting');
Route::post('/home/settings/delete', 'SettingsController@deleteSetting');
Route::post('/home/import', 'ImportController@doImport');
Route::post('/home/delete', 'HomeController@doDelete');

# cron route:
Route::get('/cron/accounts','CronController@accountCharts');
Route::get('/cron/budgets','CronController@budgetCharts');

# tasks
Route::get('/task/app', 'TaskController@doAPP');

#tags

# compare things:
Route::get('/home/compare/basictable', 'ComparisionController@basicTable');
Route::get('/home/compare/basicchart', 'ComparisionController@basicChart');
Route::get('/home/compare/categories', 'ComparisionController@compareCategories');
Route::get('/home/compare/budgets', 'ComparisionController@compareBudgets');

# pages for charts
Route::get('/home/charts/prediction', 'PageController@predictionChart');
Route::get('/home/charts/compare', 'PageController@compare');
Route::get('/home/charts/progress', 'PageController@progressPage');

# object overview:
Route::get('/home/target/overview/{id}', 'TargetController@showOverview')->where('id', '[0-9]+');
Route::get('/home/{object}/overview/{id}', 'OverviewController@showOverview')->where('id', '[0-9]+');

Route::get('/home/{object}/chart/{id}', 'OverviewController@showOverviewChart')->where('id', '[0-9]+');
Route::get('/home/{object}/pie', 'OverviewController@showPieChart');
Route::get('/home/{object}/transactions', 'OverviewController@showTransactions');

# charts themselves
Route::get('/home/chart/progress/budget', 'ChartController@budgetProgress');
Route::get('/home/chart/ovcat', 'ChartController@showOverExpendingCategories');
Route::get('/home/chart/predict', 'ChartController@predictionChart');


# account management:
Route::get('/home/account/add', 'AccountController@addAccount');
Route::get('/home/accounts', 'AccountController@showAll');
Route::get('/home/accounts/chart', 'AccountController@showAllChart');


Route::get('/home/account/overviewChart/{id}', 'AccountController@homeOverviewChart')->where('id', '[0-9]+');
Route::get('/home/account/edit/{id}', 'AccountController@editAccount')->where('id', '[0-9]+');
Route::post('/home/account/add', 'AccountController@newAccount');
Route::post('/home/account/delete/{id}', 'AccountController@deleteAccount')->where('id', '[0-9]+');
Route::post('/home/account/edit/{id}', 'AccountController@doEditAccount')->where('id', '[0-9]+');




# beneficiary management
Route::get('/home/beneficiaries', 'BeneficiaryController@showAll');
//Route::get('/home/beneficiary/overview/{id}', 'BeneficiaryController@showOverview')->where('id', '[0-9]+');
Route::get('/home/beneficiary/edit/{id}', 'BeneficiaryController@editBeneficiary')->where('id', '[0-9]+');
Route::get('/home/beneficiary/chart/{id}', 'BeneficiaryController@overviewChart')->where('id', '[0-9]+');
Route::get('/home/beneficiary/summary/{id}', 'BeneficiaryController@getBeneficiarySummary')->where('id', '[0-9]+');
Route::get('/home/beneficiary/transactions/{id}', 'BeneficiaryController@showTransactionsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/beneficiary/budgets/{id}', 'BeneficiaryController@showBudgetsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/beneficiary/categories/{id}', 'BeneficiaryController@showCategoriesInTimeframe')->where('id', '[0-9]+');
Route::post('/home/beneficiary/edit/{id}', 'BeneficiaryController@doEditBeneficiary')->where('id', '[0-9]+');
Route::post('/home/beneficiary/delete/{id}', 'BeneficiaryController@deleteBeneficiary')->where('id', '[0-9]+');


# budget management
Route::get('/home/budgets', 'BudgetController@showAll');
Route::get('/home/budget/add', 'BudgetController@addBudget');
Route::get('/home/budget/edit/{id}', 'BudgetController@editBudget');
//Route::get('/home/budget/overview/{id}', 'BudgetController@showBudgetOverview')->where('id', '[0-9]+');
Route::get('/home/budget/overviewChart/{id}', 'BudgetController@homeOverviewChart')->where('id', '[0-9]+');
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
Route::get('/home/target/overviewChart/{id}', 'TargetController@homeOverviewChart')->where('id', '[0-9]+');
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
//Route::get('/home/category/overview/{id}', 'CategoryController@showOverview')->where('id', '[0-9]+');
Route::get('/home/categories/{id}', 'CategoryController@showSingle');
Route::get('/home/category/edit/{id}', 'CategoryController@editCategory')->where('id', '[0-9]+');
Route::get('/home/category/chart/{id}', 'CategoryController@overviewChart')->where('id', '[0-9]+');
Route::get('/home/category/summary/{id}', 'CategoryController@getCategorySummary')->where('id', '[0-9]+');
Route::get('/home/category/transactions/{id}', 'CategoryController@showTransactionsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/category/budgets/{id}', 'CategoryController@showBudgetsInTimeframe')->where('id', '[0-9]+');
Route::get('/home/category/beneficiaries/{id}', 'CategoryController@showBeneficiariesInTimeframe')->where('id', '[0-9]+');
Route::post('/home/category/edit/{id}', 'CategoryController@doEditCategory')->where('id', '[0-9]+');
Route::post('/home/category/delete/{id}', 'CategoryController@deleteCategory')->where('id', '[0-9]+');
Route::get('/home/category/overspending/{id}', 'CategoryController@overSpending')->where('id', '[0-9]+');
Route::get('/home/category/overspending/{name}', 'CategoryController@overSpendingByName');



# home with a specific date.
Route::get('/home/{year?}/{month?}', 'HomeController@getHome');