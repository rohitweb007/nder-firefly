<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Add a new transfer</h3>
    <p>
      Please fill in the following details about the new transfer.
    </p>

    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputDescription">New transfer description</label>
      <div class="controls">
        <?php echo Form::text('description',null,array('id' => 'inputDescription','class' => 'input-xxlarge','autocomplete' => 'off','placeholder' => 'Moved some money to saving account')); ?>
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputBalance">Transferred amount</label>
      <div class="controls">
        <?php echo Form::input('number', 'amount',null,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => '&euro;')); ?>
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDate">Transfer date</label>
      <div class="controls">
        <?php echo Form::input('date', 'date',date('Y-m-d'),array('id' => 'inputDate','autocomplete' => 'off')); ?>
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">From account</label>
      <div class="controls">
        <?php echo Form::select('account_from',$accounts); ?>
        <br /><span class="text-error"><?php echo $errors->first('account_from'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">To account</label>
      <div class="controls">
        <?php echo Form::select('account_to',$accounts); ?>
        <br /><span class="text-error"><?php echo $errors->first('account_to'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputBudget">Falls within budget</label>
      <div class="controls">
        <?php echo Form::select('budget',$budgets); ?>
        <br /><span class="text-error"><?php echo $errors->first('budget_id'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputCategory">Category</label>
      <div class="controls">
        <?php echo Form::text('category',null,array('id' => 'inputCategory','autocomplete' => 'off','class' => 'input-large','placeholder' => 'Category','list' => 'addTargetCategory')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous categories." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('category_id'); ?></span>
        <datalist id="addTargetCategory">
          <?php foreach($categories as $cat): ?>
          <option value="<?php echo $cat; ?>"></option>
          <?php endforeach;?>
        </datalist>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputTarget">Saving target</label>
      <div class="controls">
        <?php echo Form::text('target',null,array('id' => 'inputTarget','autocomplete' => 'off','class' => 'input-large','placeholder' => 'Target','list' => 'addTransferTarget')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous categories." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('target_id'); ?></span>
        <datalist id="addTransferTarget">
          <?php foreach($targets as $t): ?>
          <option value="<?php echo $t; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>

   <div class="control-group">
      <label class="control-label" for="inputIgnorePrediction">Ignore in predictions</label>
      <div class="controls">
        <?php echo Form::checkbox('ignoreprediction',null,false,array('id' => 'inputIgnorePrediction')); ?>
        &nbsp;&nbsp;<img class="tt" title="Charts that try to guess your future balance also trigger on transfers, unless you choose to ignore them." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('ignoreprediction'); ?></span>
        <datalist id="addTransferTarget">
          <?php foreach($targets as $t): ?>
          <option value="<?php echo $t; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputCountAsExpense">Count as expense</label>
      <div class="controls">
        <?php echo Form::checkbox('countasexpense',null,false,array('id' => 'inputCountAsExpense')); ?>
        &nbsp;&nbsp;<img class="tt" title="If you cannot 'access' the money you transferred, count it as an expense to better reflect this." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('countasexpense'); ?></span>
        <datalist id="addTransferTarget">
          <?php foreach($targets as $t): ?>
          <option value="<?php echo $t; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>



    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save new transfer" />
      </div>
    </div>

    <?php echo Form::close(); ?>

  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>