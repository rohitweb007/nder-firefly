<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span2">

  </div>
  <div class="span6">
    <h3>Settings</h3>
    <p>
      On this page you can set various settings.
    </p>

    <?php echo Form::open(); ?>
    <h4>Budget behaviour</h4>
    <p>
      When you take money out of a budget it's simple: the amount left will be less.
      But what happens when you <em>add</em> money to a budget?
    </p>
    <p>
      <?php
      $opts = array('substract' => 'Substract it.', 'add'       => 'Add it.');
      echo Form::select('budgetBehaviour', $opts);
      ?>
    </p>
    <dl class="dl-horizontal">
      <dt>Substract it.</dt>
      <dd>The money is substracted from any expenses already in the budget. This leaves you with more money left.</dd>
      <dt>Add it.</dt>
      <dd>The money is added to your budget limit; ie. the budgets amount increases. This allows you to spend more on the budget.</dd>
    </dl>
    <p>
      It comes down to the same thing at the end of the day but how you want it represented is your choice.
    </p>
    <?php if (Auth::user()->accounts()->count() > 0): ?>
      <h4>Default checking account</h4>
      <p>Simple enough. This account gets selected in various screens.</p>
      <?php
      $accounts = Auth::user()->accounts()->get();
      $acc      = array();
      foreach ($accounts as $account) {
        $acc[$account->id] = Crypt::decrypt($account->name);
      }
      $setting         = Auth::user()->settings()->where('name', '=', 'defaultCheckingAccount')->first();
      $defaultChecking = null;
      if ($setting) {
        $defaultChecking = intval(Crypt::decrypt($setting->value));
      }
      echo Form::select('defaultCheckingAccount', $acc, $defaultChecking);
      ?>
    <?php endif; ?>

    <?php if (Auth::user()->accounts()->count() > 1): ?>
      <h4>Default savings account</h4>
      <p>Simple enough. This account gets selected in various screens.</p>
      <?php
      $accounts = Auth::user()->accounts()->get();
      $acc      = array();
      foreach ($accounts as $account) {
        $acc[$account->id] = Crypt::decrypt($account->name);
      }
      $setting        = Auth::user()->settings()->where('name', '=', 'defaultSavingsAccount')->first();
      $defaultSavings = null;
      if ($setting) {
        $defaultSavings = intval(Crypt::decrypt($setting->value));
      }
      echo Form::select('defaultSavingsAccount', $acc, $defaultSavings);
      ?>
    <?php endif; ?>

    <p>
      &nbsp;
    </p>
    <p>
      <input type="submit" class="btn btn-primary" value="Save settings" />
    </p>
    <?php echo Form::close(); ?>


  </div>
</div>



<script src="/js/settings.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>