<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h3>Edit transfer "<?php echo Crypt::decrypt($transfer->description); ?>"</h3>
    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputDescription">Transfer description</label>
      <div class="controls">
        <?php echo Form::text('description',Crypt::decrypt($transfer->description),array('id' => 'inputDescription','class' => 'input-xxlarge','autocomplete' => 'off','placeholder' => Crypt::decrypt($transfer->description))); ?>
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputBalance">Transferred amount</label>
      <div class="controls">
        <?php echo Form::input('number', 'amount',$transfer->amount,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => $transfer->amount)); ?>
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDate">Transfer date</label>
      <div class="controls">
        <?php echo Form::input('date', 'date',$transfer->date,array('id' => 'inputDate','autocomplete' => 'off')); ?>
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">From account</label>
      <div class="controls">
        <?php echo Form::select('account_from',$accounts,$transfer->account_from); ?>
        <br /><span class="text-error"><?php echo $errors->first('account_from'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">To account</label>
      <div class="controls">
        <?php echo Form::select('account_to',$accounts,$transfer->account_to); ?>
        <br /><span class="text-error"><?php echo $errors->first('account_to'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputBudget">Falls within budget</label>
      <div class="controls">
        <?php echo Form::select('budget',$budgets,$transfer->budget_id); ?>
        <br /><span class="text-error"><?php echo $errors->first('budget_id'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputCategory">Category</label>
      <div class="controls">
        <?php echo Form::text('category',(is_null($transfer->category_id) ? null : Crypt::decrypt($transfer->category()->first()->name)),array('id' => 'inputCategory','autocomplete' => 'off','class' => 'input-large','placeholder' => (is_null($transfer->category_id) ? 'Category' : Crypt::decrypt($transfer->category()->first()->name)),'list' => 'editTransferCategory')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous categories." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('category_id'); ?></span>
        <datalist id="editTransferCategory">
          <?php foreach($categories as $cat): ?>
          <option value="<?php echo $cat; ?>"></option>
          <?php endforeach;?>
        </datalist>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputTarget">Saving target</label>
      <div class="controls">
        <?php echo Form::text('target',(is_null($transfer->target_id) ? null : Crypt::decrypt($transfer->target()->first()->description)),array('id' => 'inputTarget','autocomplete' => 'off','class' => 'input-large','placeholder' => (is_null($transfer->target_id) ? 'Target' : Crypt::decrypt($transfer->target()->first()->description)),'list' => 'editTransferTarget')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous categories." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('target_id'); ?></span>
        <datalist id="editTransferTarget">
          <?php foreach($targets as $t): ?>
          <option value="<?php echo $t; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>

   <div class="control-group">
      <label class="control-label" for="inputIgnorePrediction">Ignore in predictions</label>
      <div class="controls">
        <?php echo Form::checkbox('ignoreprediction',null,($transfer->ignoreprediction == 1 ? true : false),array('id' => 'inputIgnorePrediction')); ?>
        &nbsp;&nbsp;<img class="tt" title="Graphs that try to guess your future balance also trigger on transfers, unless you choose to ignore them." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('ignoreprediction'); ?></span>
        <datalist id="editTransferTarget">
          <?php foreach($targets as $t): ?>
          <option value="<?php echo $t; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputCountAsExpense">Count as expense</label>
      <div class="controls">
        <?php echo Form::checkbox('countasexpense',null,($transfer->countasexpense == 1 ? true : false),array('id' => 'inputCountAsExpense')); ?>
        &nbsp;&nbsp;<img class="tt" title="If you cannot 'access' the money you transferred, count it as an expense to better reflect this." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('countasexpense'); ?></span>
        <datalist id="editTransferTarget">
          <?php foreach($targets as $t): ?>
          <option value="<?php echo $t; ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>



    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save edits to transfer" />
      </div>
    </div>

    <?php echo Form::close(); ?>

  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>