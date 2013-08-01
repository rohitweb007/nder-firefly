<?php
use Carbon\Carbon as Carbon;
class ImportController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs', array('only' => 'getHome')); // do Google "sync".
  }

  public function showImport() {
    return View::make('home.import');
  }

  public function doImport() {
    if (strlen(Input::get('payload_text')) == 0) {
      $payload = Input::file('payload');
      $raw     = File::get($payload);
    } else {
      $raw = Input::get('payload_text');
    }
    $json      = json_decode($raw, true);
    $firstIcon = Icon::first();
    $old       = array(
        Auth::user()->accounts()->get(),
        Auth::user()->budgets()->get(),
        Auth::user()->categories()->get(),
        Auth::user()->beneficiaries()->get(),
        Auth::user()->settings()->get()
    );


    $mapping = array();
    $order   = array(
        'account',
        'beneficiary',
        'budget',
        'category',
        'target',
        'transaction',
        'transfer',
        'setting'
    );
    if (is_null($json)) {
      Session::flash('error', 'This is not a valid JSON file.');
      return View::make('home.import');
    }

    foreach ($order as $name) {
      $names = Str::plural($name);
      foreach ($json[$names] as $item) {
        $class = ucfirst($name);
        // overrule user ID
        if (isset($item['fireflyuser_id'])) {
          $item['fireflyuser_id'] = Auth::user()->id;
        }

        // overrule possible account ID since we might need it here:
        if (isset($item['account_id'])) {
          $item['account_id'] = $mapping['accounts'][intval($item['account_id'])];
        }
        // overrule account_from and account_to (only for transfers)
        if ($class == 'Transfer') {
          $item['account_from'] = $mapping['accounts'][intval($item['account_from'])];
          $item['account_to']   = $mapping['accounts'][intval($item['account_to'])];
        }
        // overrule possible icon ID
        if (isset($item['icon_id'])) {
          $item['icon_id'] = intval($firstIcon->id);
        }
        // overrule possible beneficiary ID
        if (isset($item['beneficiary_id'])) {
          $item['beneficiary_id'] = $mapping['beneficiaries'][intval($item['beneficiary_id'])];
        }
        // overrule possible category ID
        if (isset($item['category_id'])) {
          $item['category_id'] = $mapping['categories'][intval($item['category_id'])];
        }
        // overrule possible budget ID
        if (isset($item['budget_id'])) {
          $item['budget_id'] = $mapping['budgets'][intval($item['budget_id'])];
        }
        // overrule possible target ID
        if (isset($item['target_id'])) {
          $item['target_id'] = $mapping['targets'][intval($item['target_id'])];
        }

        // remap settings:
        if ($class == 'Setting') {
          if ($item['name'] == 'defaultCheckingAccount') {
            $item['value'] = $mapping['accounts'][intval($item['value'])];
          }
          if ($item['name'] == 'defaultSavingsAccount') {
            $item['value'] = $mapping['accounts'][intval($item['value'])];
          }
        }

        // make validator:
        $validator = Validator::make($item, $class::$rules);
        // validate!
        if ($validator->fails()) {
          // fail gracefully, log error:
          Log::error('Validator failed on ' . $class . ': ' . print_r($validator->messages()->all(), true));
          // show error
          Session::flash('error', 'There is invalid ' . $class . ' data in the file.');
          return View::make('home.import');
        } else {
          // create the object, remember the ID.
          $object = new $class($item);
          // encrypt some fields:
          if (isset($object->description)) {
            $object->description = Crypt::encrypt($object->description);
          }
          if (isset($object->name) && $class != 'Setting') {
            $object->name = Crypt::encrypt($object->name);
          }
          if ($class == 'Setting') {
            $object->value = Crypt::encrypt($object->value);
          }
          // save:
          $object->save();

          // remember the mapping:
          $oldID                   = intval($item['id']);
          $newID                   = intval($object->id);
          $mapping[$names][$oldID] = $newID;
        }
      }
    }
    // if this is where we end up we can safely delete
    // the old accounts. Deleting those drag EVERYTHING along with it.
    foreach ($old as $items) {
      foreach ($items as $item) {
        $item->delete();
      }
    }
    Session::flash('success', 'All data imported!');
    return Redirect::to('/home');
  }

  public function doExport() {
    $filename = 'firefly-export-' . date('Y-m-d') . '.json';
    $data     = array();
    // accounts
    $accounts = Auth::user()->accounts()->get();
    foreach ($accounts as $account) {
      $account->name      = Crypt::decrypt($account->name);
      $account->balance   = floatval($account->balance);
      $data['accounts'][] = $account->toArray();
    }
    // icons
    $icons = Icon::all();
    foreach ($icons as $i) {
      $data['icons'][] = $i->toArray();
    }
    // beneficiaries
    $bene = Auth::user()->beneficiaries()->get();
    foreach ($bene as $b) {
      $b->name                 = Crypt::decrypt($b->name);
      $data['beneficiaries'][] = $b->toArray();
    }

    // budgets
    $budgets = Auth::user()->budgets()->get();
    foreach ($budgets as $budget) {
      $budget->name      = Crypt::decrypt($budget->name);
      $budget->amount    = floatval($budget->amount);
      $data['budgets'][] = $budget->toArray();
    }
    // categories
    $categories = Auth::user()->categories()->get();
    foreach ($categories as $cat) {
      $cat->name            = Crypt::decrypt($cat->name);
      $data['categories'][] = $cat->toArray();
    }
    // targets
    $targets = Auth::user()->targets()->get();
    foreach ($targets as $target) {
      $target->description = Crypt::decrypt($target->description);
      $target->amount      = floatval($target->amount);
      $data['targets'][]   = $target->toArray();
    }
    // transactions
    $transactions = Auth::user()->transactions()->get();
    foreach ($transactions as $transaction) {
      $transaction->description = Crypt::decrypt($transaction->description);
      $transaction->amount      = floatval($transaction->amount);
      $data['transactions'][]   = $transaction->toArray();
    }
    // transfers
    $transfers = Auth::user()->transfers()->get();
    foreach ($transfers as $transfer) {
      $transfer->description = Crypt::decrypt($transfer->description);
      $transfer->amount      = floatval($transfer->amount);
      $data['transfers'][]   = $transfer->toArray();
    }
    // settings:
    $settings = Auth::user()->settings()->get();
    foreach ($settings as $setting) {
      $setting->value     = Crypt::decrypt($setting->value);
      $data['settings'][] = $setting->toArray();
    }
    $payload = json_encode($data, JSON_PRETTY_PRINT);
    // We'll be outputting a PDF
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($payload));
    echo $payload;
    exit;
  }

  public function doOldImport() {

    DB::delete('DELETE FROM `cache`');



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

    $data            = file_get_contents('http://commondatastorage.googleapis.com/nder/import.json');
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
      $newIcon                         = new Icon;
      $newIcon->file                   = $icon->file;
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
    foreach ($json->categories as $c) {
      $nc                        = new Category;
      $nc->fireflyuser_id        = Auth::user()->id;
      $nc->icon_id               = intval($map['icons'][intval($c->icon_id)]);
      $nc->name                  = Crypt::encrypt($c->name);
      $nc->showtrend             = intval($c->showtrend);
      $nc->save();
      $map['categories'][$c->id] = $nc->id;
    }

    foreach ($json->targets as $t) {
      $nt                     = new Target;
      $nt->fireflyuser_id     = Auth::user()->id;
      $nt->account_id         = $map['accounts'][$t->account_id];
      $nt->description        = Crypt::encrypt($t->description);
      $nt->amount             = floatval($t->amount);
      $nt->duedate            = $t->duedate;
      $nt->startdate          = $t->startdate;
      $nt->save();
      $map['targets'][$t->id] = $nt->id;
    }

    foreach ($json->transactions as $t) {
      $nt                          = new Transaction;
      $nt->fireflyuser_id          = Auth::user()->id;
      $nt->account_id              = $map['accounts'][$t->account_id];
      $nt->budget_id               = is_null($t->budget_id) ? NULL : intval($map['budgets'][$t->budget_id]);
      $nt->category_id             = is_null($t->category_id) ? NULL : $map['categories'][$t->category_id];
      $nt->beneficiary_id          = is_null($t->beneficiary_id) ? NULL : $map['beneficiaries'][$t->beneficiary_id];
      $nt->description             = Crypt::encrypt($t->description);
      $nt->amount                  = floatval($t->amount);
      $nt->date                    = $t->date;
      $nt->onetime                 = intval($t->onetime);
      $nt->save();
      $map['transactions'][$t->id] = $nt->id;
    }

    foreach ($json->transfers as $t) {
      $nt                 = new Transfer;
      $nt->fireflyuser_id = Auth::user()->id;
      $nt->account_from   = $map['accounts'][$t->account_from];
      $nt->account_to     = $map['accounts'][$t->account_to];


      $nt->category_id = is_null($t->category_id) ? NULL : $map['categories'][$t->category_id];
      $nt->budget_id   = is_null($t->budget_id) ? NULL : intval($map['budgets'][$t->budget_id]);
      $nt->target_id   = is_null($t->target_id) ? NULL : intval($map['targets'][$t->target_id]);

      $nt->description = Crypt::encrypt($t->description);
      $nt->amount      = floatval($t->amount);
      $nt->date        = $t->date;

      $nt->ignoreprediction   = intval($t->ignoreprediction);
      $nt->countasexpense     = intval($t->countasexpense);
      $nt->save();
      $map['targets'][$t->id] = $nt->id;
    }

//
//var_dump($data);
// create everything from this file.
// we map the old id's to the new one to save problems.


    return 'Old data successfully imported.';
  }

}