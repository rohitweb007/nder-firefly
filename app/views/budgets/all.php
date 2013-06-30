<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span12">
    <h2>All your budgets!</h2>


    <?php foreach ($budgets as $period => $bdgts): ?>
      <h3><?php echo $period; ?></h3>
      <table class="table table-bordered table-condensed table-striped">
        <tr>
          <th>Description</th>
          <th>Left</th>
          <th>Amount</th>
          <th>Spent</th>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
        </tr>
        <?php foreach ($bdgts as $b): ?>
          <tr>
            <td>
              <?php echo HTML::Link('/home/budget/overview/' . $b['id'], $b['name']); ?></td>
            <td>
              <?php if($b['left'] > 0) {
                echo '<span class="text-success">'.mf($b['left']).'</span>';
              } else {
                echo '<span class="text-error">'.mf($b['left']).'</span>';
              }
              ?>
            </td>

            <td><?php echo mf($b['amount']); ?></td>
            <td><?php echo mf($b['spent']); ?></td>
            <td style="width:300px;">
              <?php if ($b['amount'] != 0): ?>
                <?php if ($b['overspent'] === false): ?>
                  <div class="progress progress-striped">
                    <div class="bar bar-success" style="width: <?php echo $b['pct']; ?>%;"></div>
                  </div>
                <?php else: ?>
                  <div class="progress progress-striped">
                    <div class="bar bar-warning" style="width: <?php echo $b['pct']; ?>%;"></div>
                    <div class="bar bar-danger" style="width: <?php echo (100 - $b['pct']); ?>%;"></div>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </td>


            <td style="width:100px;">
              <a href="/home/budget/edit/<?php echo $b['id'];?>" class="btn"><i class="icon-pencil"></i></a>
              <a href="#" data-value="<?php echo $b['id']; ?>" title="Delete <?php echo $b['name']; ?>" class="btn btn-danger deleteBudget"><i data-value="<?php echo $b['id']; ?>" class="icon-white icon-remove"></i></a>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endforeach; ?>

  </div>
</div>

<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete  "<span id="delBudgetName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url'   => '/home/budget/delete', 'style' => 'display:inline;', 'id'    => 'delBudgetForm')); ?>

    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/budget.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>