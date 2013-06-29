<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span12">
    <h2>All transactions you ever made!</h2>


      <?php foreach ($transactions as $period => $tr): ?>
        <h3><?php echo $period;?></h3>
        <table class="table table-bordered table-condensed table-striped">
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Account</th>
            <th>Budget</th>
            <th>Category</th>
            <th>Beneficiary</th>
            <th>&nbsp;</th>
          </tr>
        <?php foreach($tr as $t):?>
        <tr>
          <td><?php echo $t['date']; ?></td>
          <td><?php echo HTML::Link('/home/transaction/edit/'.$t['id'],$t['description']); ?></td>
          <td><?php echo $t['amount'] ?></td>
          <td><?php echo HTML::Link('/home/account/overview/' . $t['account_id'],$t['account_name']);?></td>
          <td><?php echo !is_null($t['budget_id']) ? HTML::Link('/home/budget/overview/' . $t['budget_id'],$t['budget_name']) : '';?></td>
          <td><?php echo !is_null($t['category_id']) ? HTML::Link('/home/category/overview/' . $t['category_id'],$t['category_name']) : '';?></td>
          <td><?php echo !is_null($t['beneficiary_id']) ? HTML::Link('/home/beneficiary/overview/' . $t['beneficiary_id'],$t['beneficiary_name']) : '';?></td>
          <td><a href="/home/transaction/delete/<?php echo $t['id']; ?>" title="Delete <?php echo $t['description'];?>" class="btn btn-danger"><i class="icon-white icon-remove"></i></a>
        </tr>
        <?php endforeach;?>
</table>
<?php endforeach;?>

  </div>
</div>

<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>