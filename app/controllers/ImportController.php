<?php

class ImportController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs', array('only' => 'getHome')); // do Google "sync".
  }

  public function doImport() {

    // delete old data:
    foreach (Auth::user()->accounts()->get() as $acc) {
      $acc->delete();
    }

    foreach (Auth::user()->budgets()->get() as $b) {
      $b->delete();
    }

    foreach (Auth::user()->categories()->get() as $b) {
      $b->delete();
    }
    foreach (Auth::user()->beneficiaries()->get() as $b) {
      $b->delete();
    }

    foreach (Icon::get() as $icon) {
      $icon->delete();
    }

    $data            = file_get_contents('/Library/WebServer/Documents/import.json');
    $json            = json_decode($data);
    $map             = array();
    $map['accounts'] = array();
    $map['icons']    = array();

    // all accounts:
    foreach ($json->accounts as $account) {
      $newAccount                    = new Account;
      $newAccount->name              = Crypt::encrypt($account->name);
      $newAccount->balance           = floatval($account->balance);
      $newAccount->fireflyuser_id    = Auth::user()->id;
      $newAccount->date              = $account->date;
      $newAccount->save();
      $map['accounts'][$account->id] = $newAccount->id;
    }

    // all icons:
    foreach ($json->icons as $icon) {
      $newIcon                 = new Icon;
      $newIcon->file           = $icon->file;
      $newIcon->save();
      $map['icons'][intval($icon->id)] = $newIcon->id;
    }

    // all beneficiaries:
    foreach ($json->beneficiaries as $ben) {
      $nb                             = new Beneficiary;
      $nb->fireflyuser_id             = Auth::user()->id;
      $nb->name                       = Crypt::encrypt($ben->name);
      $nb->save();
      $map['beneficiaries'][$ben->id] = $nb->id;
    }

    // all budgets
    foreach ($json->budgets as $bd) {
      $nbg                     = new Budget;
      $nbg->fireflyuser_id     = Auth::user()->id;
      $nbg->name               = Crypt::encrypt($bd->name);
      $nbg->date               = $bd->date;
      $nbg->amount             = floatval($bd->amount);
      $nbg->save();
      $map['budgets'][$bd->id] = $nbg->id;
    }

    // all categories:
    foreach($json->categories as $c) {
      $nc = new Category;
      $nc->fireflyuser_id = Auth::user()->id;
      $nc->icon_id = intval($map['icons'][intval($c->icon_id)]);
      $nc->name = Crypt::encrypt($c->name);
      $nc->showtrend = intval($c->showtrend);
      $nc->save();
      $map['categories'][$c->id] = $nc->id;
    }

    foreach($json->targets as $t) {
      $nt = new Target;
      $nt->fireflyuser_id = Auth::user()->id;
      $nt->account_id = $map['accounts'][$t->account_id];
      $nt->description = Crypt::encrypt($t->description);
      $nt->amount = floatval($t->amount);
      $nt->duedate = $t->duedate;
      $nt->startdate = $t->startdate;
      $nt->save();
      $map['targets'][$t->id] = $nt->id;
    }

    foreach($json->transactions as $t) {
      $nt = new Transaction;
      $nt->fireflyuser_id = Auth::user()->id;
      $nt->account_id = $map['accounts'][$t->account_id];
      $nt->budget_id = is_null($t->budget_id) ? NULL : intval($map['budgets'][$t->budget_id]);
      $nt->category_id = is_null($t->category_id) ? NULL : $map['categories'][$t->category_id];
      $nt->beneficiary_id = is_null($t->beneficiary_id) ? NULL : $map['beneficiaries'][$t->beneficiary_id];
      $nt->description = Crypt::encrypt($t->description);
      $nt->amount = floatval($t->amount);
      $nt->date = $t->date;
      $nt->onetime = intval($t->onetime);
      $nt->save();
      $map['transactions'][$t->id] = $nt->id;
    }

    foreach($json->transfers as $t) {
      $nt = new Transfer;
      $nt->fireflyuser_id = Auth::user()->id;
      $nt->account_from = $map['accounts'][$t->account_from];
      $nt->account_to = $map['accounts'][$t->account_to];


      $nt->category_id = is_null($t->category_id) ? NULL : $map['categories'][$t->category_id];
      $nt->budget_id = is_null($t->budget_id) ? NULL : intval($map['budgets'][$t->budget_id]);
      $nt->target_id = is_null($t->target_id) ? NULL : intval($map['targets'][$t->target_id]);

      $nt->description = Crypt::encrypt($t->description);
      $nt->amount = floatval($t->amount);
      $nt->date = $t->date;

      $nt->ignoreprediction = intval($t->ignoreprediction);
      $nt->countasexpense = intval($t->countasexpense);
      $nt->save();
      $map['targets'][$t->id] = $nt->id;

    }

    var_dump($map);
    //var_dump($data);

    // create everything from this file.
    // we map the old id's to the new one to save problems.


    return 'x;';
  }

}