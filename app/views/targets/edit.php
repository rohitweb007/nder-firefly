<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Edit target "<?php echo Crypt::decrypt($target->description);?>"</h3>
    <?php echo Form::open(array('class' => 'form-horizontal'));?>
    <div class="control-group">
      <label class="control-label" for="inputDescription">Target description</label>
      <div class="controls">
        <?php echo Form::text('description',Crypt::decrypt($target->description),array('id' => 'inputDescription','autocomplete' => 'off','class' => 'input-xxlarge','placeholder' => Crypt::decrypt($target->description)));?>
        <br /><span class="text-error"><?php echo $errors->first('description'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputAmount">Target saving amount</label>
      <div class="controls">
        <?php echo Form::input('number', 'amount',$target->amount,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => $target->amount));?>
        &nbsp;&nbsp;<img  class="tt" title="How much do you want to save? Enter 0 for no limit." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputStartdate">Start date</label>
      <div class="controls">
        <?php echo Form::input('date', 'startdate',$target->startdate,array('id' => 'inputStartdate','autocomplete' => 'off','placeholder' => $target->startdate));?>
        &nbsp;&nbsp;<img class="tt" title="When did you start saving? Defaults to today" src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('startdate'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDuedate">Due date</label>
      <div class="controls">
        <?php echo Form::input('date', 'duedate',$target->duedate,array('id' => 'inputDuedate','autocomplete' => 'off','placeholder' => $target->duedate));?>
        &nbsp;&nbsp;<img  class="tt" title="When do you want to have the money collected? Optional field." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('duedate'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">Target saving account</label>
      <div class="controls">
        <?php echo Form::select('account',$accounts,$target->account_id);?>
        <br /><span class="text-error"><?php echo $errors->first('account_id'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save changes to <?php echo Crypt::decrypt($target->description);?>" />
      </div>
    </div>

    <?php echo Form::close();?>

  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>