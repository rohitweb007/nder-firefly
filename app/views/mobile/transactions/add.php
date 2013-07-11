<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h2>Add a new transaction</h2>
    <?php echo Form::open(); ?>
    <p>
      Description:<br />
      <?php echo Form::text('description', null, array('id'           => 'inputDescription', 'autocomplete' => 'off', 'class'        => 'input-xxlarge', 'placeholder'  => 'Transaction description')); ?><br />
      <span class="text-error"><?php echo $errors->first('description'); ?></span>
    </p>
    <p>
      Amount:<br />
      <?php echo Form::input('number', 'amount', null, array('step'         => 'any', 'autocomplete' => 'off', 'id'           => 'inputAmount', 'placeholder'  => '&euro;')); ?><br />
      <span class="text-error"><?php echo $errors->first('amount'); ?></span>
    </p>
    <p>
      Date:<br />
      <?php echo Form::input('date', 'date', date('Y-m-d'), array('id'           => 'inputDate', 'autocomplete' => 'off')); ?>
      <span class="text-error"><?php echo $errors->first('date'); ?></span>
    </p>
    <p>
      One time: <?php echo Form::checkbox('onetime', null, false, array('id' => 'inputOnetime')); ?>
      <br /><span class="text-error"><?php echo $errors->first('onetime'); ?></span>
    </p>
    <p>Account:<br />
      <?php echo Form::select('account', $accounts); ?>
      <br /><span class="text-error"><?php echo $errors->first('account_id'); ?></span>
    </p>
    <p>Budget:<br />
      <?php echo Form::select('budget', $budgets); ?>
      <br /><span class="text-error"><?php echo $errors->first('budget_id'); ?></span>
    </p>
    <p>
      Category:<br />
      <?php echo Form::text('category', null, array('id'           => 'inputCategory', 'autocomplete' => 'off', 'class'        => 'input-large', 'placeholder'  => 'Category')); ?>
      <br /><span class="text-error"><?php echo $errors->first('category_id'); ?></span>
    </p>
    <p>
      Beneficiary:<br />
      <?php echo Form::text('beneficiary', null, array('id'           => 'inputBeneficiary', 'autocomplete' => 'off', 'class'        => 'input-large', 'placeholder'  => 'Beneficiary')); ?>
    </p>
    <p>
      <input type="submit" class="btn btn-primary" value="Save new transaction" />
    </p>
    <?php echo Form::close(); ?>
  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>