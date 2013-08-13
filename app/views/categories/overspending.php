<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span8">
    <h3>Category <?php echo Crypt::decrypt($category->name); ?></h3>

    <p>
      This overview shows you why you seem to be overspending on category "<?php echo Crypt::decrypt($category->name); ?>".
      Note that transactions that are marked as "one time" are not counted, even though they are listed for clarity. This
      also goes for transfers that are not marked as expenses; they are listed as well.

  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <table class="table">
      <tr>
        <th>Type</th>
        <th>Date</th>
        <th>Description</th>
        <th>Amount</th>
        <th>Account</th>
        <th>Budget</th>
        <th>Beneficiary</th>
        <th></th>
      </tr>
      <?php foreach ($data['transactions'] as $t): ?>
        <tr>
          <td>Transaction</td>
          <td><?php echo $t['date']->format('j F Y'); ?></td>
          <td>
            <?php
            if ($t['onetime']) {
              echo '<i class="icon-download-alt"></i> ';
            }
            ?>
            <?php echo HTML::Link('/home/transaction/edit/' . $t['id'], $t['description']); ?></td>
          <td><?php echo mf($t['amount']); ?></td>
          <td><?php echo HTML::Link('/home/account/overview/' . $t['account_id'], $t['account_name']); ?></td>
          <td><?php echo!is_null($t['budget_id']) ? HTML::Link('/home/budget/overview/' . $t['budget_id'], $t['budget_name']) : ''; ?></td>
          <td><?php echo!is_null($t['beneficiary_id']) ? HTML::Link('/home/beneficiary/overview/' . $t['beneficiary_id'], $t['beneficiary_name']) : ''; ?></td>
          <td>
            <a href="/home/transaction/edit/<?php echo $t['id']; ?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#"  data-value="<?php echo $t['id']; ?>" title="Delete <?php echo $t['description']; ?>" class="btn btn-danger deleteTransaction"><i data-value="<?php echo $t['id']; ?>" class="icon-white icon-remove"></i></a>
        </tr>
        </tr>
      <?php endforeach; ?>
      <?php foreach ($data['transfers'] as $t): ?>
        <tr>
          <td>Transfer</td>
          <td><?php echo $t['date']->format('j F Y'); ?></td>
          <td>
            <?php
            if ($t['ignoreprediction']) {
              echo '<i class="icon-eye-close" title="Ignore in predictions" alt="Ignore in predictions"></i> ';
            }
            if ($t['countasexpense']) {
              echo '<i class="icon-shopping-cart" title="Count as expense" alt="Count as expense"></i> ';
            }
            ?>
            <?php echo HTML::Link('/home/transfer/edit/' . $t['id'], $t['description']); ?></td>
          <td><?php echo $t['amount'] ?></td>
          <td>
            <?php echo HTML::Link('/home/account/overview/' . $t['account_from'], $t['account_from_name']); ?>
            &rarr;
            <?php echo HTML::Link('/home/account/overview/' . $t['account_to'], $t['account_to_name']); ?>
          </td>
          <td><?php echo!is_null($t['budget_id']) ? HTML::Link('/home/budget/overview/' . $t['budget_id'], $t['budget_name']) : ''; ?></td>
          <td><?php echo!is_null($t['target_id']) ? HTML::Link('/home/target/overview/' . $t['target_id'], $t['target_description']) : ''; ?></td>
          <td>
            <a href="/home/transfer/edit/<?php echo $t['id']; ?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#"  data-value="<?php echo $t['id']; ?>" title="Delete <?php echo $t['description']; ?>" class="btn btn-danger deleteTransfer"><i data-value="<?php echo $t['id']; ?>" class="icon-white icon-remove"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="3"><em>Sum of all expenses counted:</em></td>
        <td colspan="5"><em><?php echo mf($data['sum']); ?></em></td>
      </tr>
    </table>
  </div>
</div>
<div class="row-fluid">
  <div class="span6">
    <h4>Months for this category</h4>
    <p>Please note that this chart is corrected for the day of the month.
      It only counts up until the <?php echo Session::get('period')->format('jS'); ?> of <?php echo Session::get('period')->format('F Y'); ?>.
    <table class="table table-bordered table-striped">
      <tr>
        <th>Month</th>
        <th>Spent</th>
      </tr>
      <?php foreach ($data['past'] as $r): ?>
        <tr>
          <td><?php echo $r['date']; ?></td>
          <td><?php echo HTML::Link('/home/category/overview/' . $category->id.'?start='.$r['start_date'].'&amp;end=' . $r['end_date'],mf($r['spent'])); ?></td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td><strong>Average</strong></td>
        <td><strong><?php echo mf($data['average']); ?></strong></td>
      </tr>
    </table>
  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>