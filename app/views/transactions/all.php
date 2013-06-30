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
          <td>
            <?php if($t['onetime']) {
              echo '<i class="icon-download-alt"></i> ';
            }
            ?>
            <?php echo HTML::Link('/home/transaction/edit/'.$t['id'],$t['description']); ?></td>
          <td><?php echo $t['amount'] ?></td>
          <td><?php echo HTML::Link('/home/account/overview/' . $t['account_id'],$t['account_name']);?></td>
          <td><?php echo !is_null($t['budget_id']) ? HTML::Link('/home/budget/overview/' . $t['budget_id'],$t['budget_name']) : '';?></td>
          <td><?php echo !is_null($t['category_id']) ? HTML::Link('/home/category/overview/' . $t['category_id'],$t['category_name']) : '';?></td>
          <td><?php echo !is_null($t['beneficiary_id']) ? HTML::Link('/home/beneficiary/overview/' . $t['beneficiary_id'],$t['beneficiary_name']) : '';?></td>
          <td>
            <a href="/home/transaction/edit/<?php echo $t['id'];?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#"  data-value="<?php echo $t['id']; ?>" title="Delete <?php echo $t['description'];?>" class="btn btn-danger deleteTransaction"><i data-value="<?php echo $t['id']; ?>" class="icon-white icon-remove"></i></a>
        </tr>
        <?php endforeach;?>
</table>
<?php endforeach;?>

  </div>
</div>

<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete "<span id="delTransactionName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url' => '/home/transaction/delete','style' => 'display:inline;','id' => 'delTransactionForm')); ?>

    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/transaction.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>