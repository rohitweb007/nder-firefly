<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Edit budget "<?php echo Crypt::decrypt($budget->name); ?>"</h3>

    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputName">New budget name</label>
      <div class="controls">
        <?php echo Form::text('name',Crypt::decrypt($budget->name),array('id' => 'inputName','autocomplete' => 'off','placeholder' => Crypt::decrypt($budget->name))); ?>
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputAmount">Budget amount</label>
      <div class="controls">
        <?php echo Form::input('number', 'amount',$budget->amount,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => $budget->amount)); ?>
        &nbsp;&nbsp;<img class="tt" title="Enter 0 to give the budget no limit." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDate">Budgets period</label>
      <div class="controls">
        <?php echo Form::select('date',$dates,$budget->date); ?>
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save edits to <?php echo Crypt::decrypt($budget->name); ?>" />
      </div>
    </div>

    <?php echo Form::close(); ?>

  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/top.php') ?>