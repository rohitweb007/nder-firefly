<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span12">
    <h3>All transfers</h3>


      <?php foreach ($transfers as $period => $tr): ?>
        <h4><?php echo $period;?></h4>
        <table class="table table-bordered table-condensed table-striped">
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Accounts</th>
            <th>Budget</th>
            <th>Category</th>
            <th>Target</th>
            <th>&nbsp;</th>
          </tr>
        <?php foreach($tr as $t):?>
        <tr>
          <td><?php echo $t['date']; ?></td>
          <td>
            <?php
            if($t['ignoreprediction']) {
              echo '<i class="icon-eye-close" title="Ignore in predictions" alt="Ignore in predictions"></i> ';
            }
            if($t['countasexpense']) {
              echo '<i class="icon-shopping-cart" title="Count as expense" alt="Count as expense"></i> ';
            }
            ?>

            <?php echo HTML::Link('/home/transfer/edit/'.$t['id'],$t['description']); ?></td>
          <td><?php echo $t['amount'] ?></td>
          <td>
            <?php echo HTML::Link('/home/account/overview/' . $t['account_from'],$t['account_from_name']);?>
            &rarr;
            <?php echo HTML::Link('/home/account/overview/' . $t['account_to'],$t['account_to_name']);?>
            </td>
          <td><?php echo !is_null($t['budget_id']) ? HTML::Link('/home/budget/overview/' . $t['budget_id'],$t['budget_name']) : '';?></td>
          <td><?php echo !is_null($t['category_id']) ? HTML::Link('/home/category/overview/' . $t['category_id'],$t['category_name']) : '';?></td>
          <td><?php echo !is_null($t['target_id']) ? HTML::Link('/home/target/overview/' . $t['target_id'],$t['target_description']) : '';?></td>
          <td>
            <a href="/home/transfer/edit/<?php echo $t['id'];?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#"  data-value="<?php echo $t['id']; ?>" title="Delete <?php echo $t['description'];?>" class="btn btn-danger deleteTransfer"><i data-value="<?php echo $t['id']; ?>" class="icon-white icon-remove"></i></a>
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
      Are you sure you want delete to "<span id="delTransferName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url' => '/home/transfer/delete','style' => 'display:inline;','id' => 'delTransferForm')); ?>

    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/transfer.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>