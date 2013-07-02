<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Edit "<?php echo Crypt::decrypt($transaction->description); ?>"</h3>
    <p>

    </p>

    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputDescription">Description</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('description', Crypt::decrypt($transaction->description), array('id'           => 'inputDescription', 'autocomplete' => 'off', 'class'        => 'input-xxlarge', 'placeholder'  => Crypt::decrypt($transaction->description))); ?>
        <br /><span class="text-error"><?php echo $errors->first('description'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAmount">Amount</label>
      <div class="controls">

        <?php
        if ($transaction->amount < 0) {
          echo '<i class="icon-minus-sign toggle-icon"></i>';
        } else {
          echo '<i class="icon-plus-sign toggle-icon"></i>';
        }
        if ($transaction->amount < 0) {
          $amount = $transaction->amount * -1;
        } else {
          $amount = $transaction->amount;
        }
        ?>

        &nbsp;<?php echo Form::input('number', 'amount', $amount, array('step'         => 'any', 'autocomplete' => 'off', 'id'           => 'inputAmount', 'placeholder'  => $transaction->amount)); ?>
        &nbsp;&nbsp;<img  class="tt" title="Enter the amount for this transaction." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>



    <div class="control-group">
      <label class="control-label" for="inputDate">Transaction date</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::input('date', 'date', $transaction->date, array('id'           => 'inputDate', 'autocomplete' => 'off')); ?>
        &nbsp;&nbsp;<img  class="tt" title="When did the transaction occur?" src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputOnetime">Check if this is a one time transaction</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::checkbox('onetime', null, ($transaction->onetime == 1 ? true : false), array('id' => 'inputOnetime')); ?>
        &nbsp;&nbsp;<img class="tt" title="One-time transactions are large and occur rarely. Maybe a big new TV or something else expensive." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('onetime'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">
        <?php if ($transaction->amount < 0): ?>
          From account
        <?php else: ?>
          Into account
        <?php endif; ?>
      </label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::select('account', $accounts, $transaction->account_id); ?>
        <br /><span class="text-error"><?php echo $errors->first('account_id'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputBudget">
        <?php if ($transaction->amount < 0): ?>
          From budget
        <?php else: ?>
          Into budget
        <?php endif; ?>
      </label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::select('budget', $budgets, $transaction->budget_id); ?>
        <br /><span class="text-error"><?php echo $errors->first('budget_id'); ?></span>
      </div>
    </div>



    <div class="control-group">
      <label class="control-label" for="inputCategory">Category</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('category', (is_null($transaction->category_id) ? null : Crypt::decrypt($transaction->category()->first()->name)), array('id'           => 'inputCategory', 'autocomplete' => 'off', 'class'        => 'input-large', 'placeholder'  => (is_null($transaction->category_id) ? 'Category' : Crypt::decrypt($transaction->category()->first()->name)), 'list'         => 'editTransactionCategory')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous categories." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('category_id'); ?></span>
        <datalist id="editTransactionCategory">
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputBeneficiary">
        <?php if ($transaction->amount < 0): ?>
          Beneficiary
        <?php else: ?>
          Payee
        <?php endif; ?>
      </label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('beneficiary', (is_null($transaction->beneficiary_id) ? null : Crypt::decrypt($transaction->beneficiary()->first()->name)), array('id'           => 'inputBeneficiary', 'autocomplete' => 'off', 'class'        => 'input-large', 'placeholder'  => (is_null($transaction->beneficiary_id) ? 'Beneficiary' : Crypt::decrypt($transaction->beneficiary()->first()->name)), 'list'         => 'editTransactionBeneficiary')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous beneficiaries." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('beneficiary_id'); ?></span>
        <datalist id="editTransactionBeneficiary">
          <?php foreach ($beneficiaries as $ben): ?>
            <option value="<?php echo $ben; ?>"></option>
          <?php endforeach; ?>
        </datalist>

      </div>
    </div>




    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save new transaction" />
      </div>
    </div>
    <?php
    if ($transaction->amount < 0) {
      echo Form::hidden('type', 'min', array('id' => 'toggle-value'));
    } else {
      echo Form::hidden('type', 'plus', array('id' => 'toggle-value'));
    }
    ?>
    <?php echo Form::close(); ?>

  </div>
</div>
<script src="/js/transaction.js"></script>


<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>