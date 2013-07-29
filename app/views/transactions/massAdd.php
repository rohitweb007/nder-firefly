<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h3>Add a lot of transactions.</h3>

    <?php echo Form::open(); ?>
    <table class="table table-striped">
      <tr>
        <th>Description</th>
        <th>Amount</th>
        <th>Date</th>
        <th>One time</th>
        <th>From account</th>
        <th>Budget</th>
        <th>Category</th>
        <th>Beneficiary</th>
      </tr>
      <?php for ($i = 0; $i < 30; $i++): ?>
        <tr>
          <td><?php echo Form::text('transactions[' . $i . '][description]', null, array('autocomplete' => 'off', 'class'        => 'input-xlarge', 'placeholder'  => 'Transaction description')); ?></td>
          <td><?php echo Form::input('number', 'transactions[' . $i . '][amount]', null, array('step'         => 'any', 'autocomplete' => 'off', 'placeholder'  => '&euro;')); ?></td>
          <td><?php echo Form::input('date', 'transactions[' . $i . '][date]', date('Y-m-d'), array('autocomplete' => 'off')); ?></td>
          <td><?php echo Form::checkbox('transactions[' . $i . '][onetime]', null, false, array()); ?></td>
          <td><?php echo Form::select('transactions[' . $i . '][account]', $accounts); ?></td>
          <td><?php echo Form::select('transactions[' . $i . '][budget]', $budgets); ?></td>
          <td><?php echo Form::text('transactions[' . $i . '][category]', null, array('autocomplete' => 'off', 'class'        => 'input', 'placeholder'  => 'Category', 'list'         => 'addTransactionCategory')); ?></td>
          <td><?php echo Form::text('transactions[' . $i . '][beneficiary]', null, array('autocomplete' => 'off', 'class'        => 'input-large', 'placeholder'  => 'Beneficiary', 'list'         => 'addTransactionBeneficiary')); ?></td>
        </tr>




      <?php endfor; ?>
    </table>


    <datalist id="addTransactionCategory">
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat; ?>"></option>
      <?php endforeach; ?>
    </datalist>

    <datalist id="addTransactionBeneficiary">
      <?php foreach ($beneficiaries as $ben): ?>
        <option value="<?php echo $ben; ?>"></option>
      <?php endforeach; ?>
    </datalist>
    <p>
      <input type="submit" class="btn btn-primary" value="Save everything" />
    </p>
    <?php echo Form::close(); ?>

  </div>
</div>

<script src="/js/transaction.js"></script>

<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>