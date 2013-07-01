<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Edit "<?php echo Crypt::decrypt($beneficiary->name); ?>"</h3>
    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputName">Account name</label>
      <div class="controls">
        <?php echo Form::text('name', Crypt::decrypt($beneficiary->name), array('id'           => 'inputName', 'autocomplete' => 'off', 'placeholder'  => Crypt::decrypt($beneficiary->name))); ?>
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save beneficiary" />
      </div>
    </div>
    <?php echo Form::close(); ?>
  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>