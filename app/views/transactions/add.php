<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span8">
    <h3>Add a new transaction</h3>
    <p>
      Please fill in the following details about the new transaction. It can be both
      income and expense; use the icon next to the amount field to signify the difference.
    </p>
    <p>
      Categories and beneficiaries / payees (who you paid / who's paying you) are auto-saved, meaning
      that whatever you fill in will be saved. If you have a bunch of them, the text-box will suggest
      existing ones.
    </p>
    <p>
      <?php echo HTML::Link('/home/transaction/add/mass','Add transactions en masse');?>
    </p>

    <?php echo Form::open(array('class' => 'form-horizontal')); ?>
    <div class="control-group">
      <label class="control-label" for="inputDescription">Description</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('description',null,array('id' => 'inputDescription','autocomplete' => 'off','class' => 'input-xxlarge','placeholder' => 'Transaction description')); ?>
        <br /><span class="text-error"><?php echo $errors->first('description'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAmount">Amount</label>
      <div class="controls">
        <i class="icon-minus-sign toggle-icon"></i>&nbsp;&nbsp;<?php echo Form::input('number', 'amount',null,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => '&euro;')); ?>
        &nbsp;&nbsp;<img  class="tt" title="Enter the amount for this transaction." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>



    <div class="control-group">
      <label class="control-label" for="inputDate">Transaction date</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::input('date', 'date',date('Y-m-d'),array('id' => 'inputDate','autocomplete' => 'off')); ?>
        &nbsp;&nbsp;<img  class="tt" title="When did the transaction occur?" src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputOnetime">One time</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::checkbox('onetime',null,false,array('id' => 'inputOnetime')); ?>
        &nbsp;&nbsp;<img class="tt" title="One-time transactions are large and occur rarely. Maybe a big new TV or something else expensive." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('onetime'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">From account</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::select('account',$accounts); ?>
        <br /><span class="text-error"><?php echo $errors->first('account_id'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputBudget">From budget</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::select('budget',$budgets); ?>
        <br /><span class="text-error"><?php echo $errors->first('budget_id'); ?></span>
      </div>
    </div>



    <div class="control-group">
      <label class="control-label" for="inputCategory">Category</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('category',null,array('id' => 'inputCategory','autocomplete' => 'off','class' => 'input-large','placeholder' => 'Category','list' => 'addTransactionCategory')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous categories." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('category_id'); ?></span>
        <datalist id="addTransactionCategory">
          <?php foreach($categories as $cat): ?>
          <option value="<?php echo $cat; ?>"></option>
          <?php endforeach;?>
        </datalist>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputBeneficiary">Beneficiary</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('beneficiary',null,array('id' => 'inputBeneficiary','autocomplete' => 'off','class' => 'input-large','placeholder' => 'Beneficiary','list' => 'addTransactionBeneficiary')); ?>
        &nbsp;&nbsp;<img class="tt" title="This is a free field, which will suggest previous beneficiaries." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('beneficiary_id'); ?></span>
        <datalist id="addTransactionBeneficiary">
          <?php foreach($beneficiaries as $ben):?>
            <option value="<?php echo $ben; ?>"></option>
          <?php endforeach;?>
        </datalist>

      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputTags">Tags</label>
      <div class="controls">
        <i class="icon-bar"></i>&nbsp;&nbsp;<?php echo Form::text('tags',null,array('id' => 'inputTags','autocomplete' => 'off','class' => 'input-xxlarge','placeholder' => 'Type tags here','data-role' => 'tagsinput')); ?>
        <br /><span class="text-error"><?php echo $errors->first('tags'); ?></span>
      </div>
    </div>




    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save new transaction" />
      </div>
    </div>
    <?php echo Form::hidden('type','min',array('id' => 'toggle-value')); ?>
    <?php echo Form::close(); ?>

  </div>
</div>

<script src="/js/transaction.js"></script>

<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>