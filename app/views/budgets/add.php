<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Add a new budget</h3>
    <p>
      Please fill in the following details about your new budget.

      You can track transactions and transfers using this budget. Give it a set
      maximum amount for this month or give it no limit at all.
    </p>

    <p>
      <em>Budgets need to be recreated montly. While this can be a chore, and automation
      is on its way, it is an easy way to manage your money.</em>
    </p>
    <p>
      This budget will be created for <?php echo Session::get('period')->format('F Y'); ?>.
    </p>

    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputName">New budget name</label>
      <div class="controls">
        <?php echo Form::text('name',null,array('id' => 'inputName','autocomplete' => 'off','placeholder' => 'Budget Name')); ?>
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputAmount">Budgets amount</label>
      <div class="controls">
        <?php echo Form::input('number', 'amount',null,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => '&euro;')); ?>
        &nbsp;&nbsp;<img class="tt" title="Enter 0 to give the budget no limit." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save new budget" />
      </div>
    </div>

    <?php echo Form::close(); ?>

  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/top.php') ?>