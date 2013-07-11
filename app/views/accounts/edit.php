<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Edit "<?php echo Crypt::decrypt($account->name); ?>"</h3>
    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputName">Account name</label>
      <div class="controls">
        <?php echo Form::text('name', Crypt::decrypt($account->name), array('id'           => 'inputName', 'autocomplete' => 'off', 'placeholder'  => Crypt::decrypt($account->name))); ?>
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputBalance">Account's opening balance</label>
      <div class="controls">
        <?php echo Form::input('number', 'balance', $account->balance, array('step'         => 'any', 'autocomplete' => 'off', 'id'           => 'inputBalance', 'placeholder'  => $account->balance)); ?>
        &nbsp;&nbsp;<img class="tt" title="Enter the account's current balance." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('balance'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDate">Balance date</label>
      <div class="controls">
        <?php echo Form::input('date', 'date', $account->date, array('id'           => 'inputDate', 'autocomplete' => 'off', 'placeholder'  => $account->date)); ?>
        &nbsp;&nbsp;<img class="tt" title="When did you last check this balance?" src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save edits to <?php echo Crypt::decrypt($account->name); ?>" />
      </div>
    </div>
    <?php echo Form::close(); ?>
  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>