<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">

    <?php
    if (count($data['accounts']) > 0) {
      echo '<h4>Accounts</h4><table class="table table-condensed table-striped">';
      foreach ($data['accounts'] as $account):
        ?>
        <tr>
          <th colspan="2">
            <?php
            echo HTML::Link('/home/account/overview/' . $account['id'], $account['name'], $account['header']);
            ?>
          </th>
          <td>
            <?php echo '<small style="font-weight:normal;">' . mf($account['currentbalance']) . '</small>'; ?>
          </td>
        </tr>
        <tr>
          <td colspan="3">
            <div class="accountOverviewGraph loading" data-value="<?php echo $account['id']; ?>" id="accountOverviewGraph<?php echo $account['id']; ?>"></div>
          </td>
        </tr>
      <?php endforeach;
      ?>
      <tr>
        <td colspan="2" style="text-align:right;"><em>Total:</em></td>
        <td><?php echo mf($data['acc_data']['sum']); ?></td>
      </tr>
      </table>
    <?php } ?>
  </div>

  <?php if (count($data['accounts']) > 0): ?>
    <div class="span6">
      <h4>Budgets</h4>
      <?php if (count($data['budgets']) < 1): ?>
        <p>
          <em>Your next step should be to add a budget. A budget can help you accurately track expenses.</em><br />
          <?php echo HTML::link('/home/budget/add', 'Create a budget now.'); ?>
        </p>
      <?php else: ?>
        <table class="table table-condensed table-striped">
          <?php $sum   = 0;
          $spent = 0;
          foreach ($data['budgets'] as $budget): $sum += $budget['amount'];
            $spent += $budget['spent']; ?>

            <tr>
              <th style="width:30%;">
                <?php if ($budget['overflow'] === false): ?>
                  <a href="/home/budget/overview/<?php echo $budget['id']; ?>"><?php echo $budget['name']; ?></a>
      <?php else: ?>
                  <a href="/home/budget/overview/<?php echo $budget['id']; ?>" class="tt" title="Firefly predicts you will overspend on this budget!"><?php echo $budget['name']; ?></a>
      <?php endif; ?>
              </th>
              <td><small><?php echo mf($budget['amount']); ?> - <?php echo mf($budget['spent']); ?> = <?php echo mf($budget['left']); ?></small></td>
            </tr>
            <tr>
              <td colspan="3">
                <div class="budgetOverviewGraph loading" data-value="<?php echo $budget['id']; ?>" id="budgetOverviewGraph<?php echo $budget['id']; ?>"></div>
              </td>
            </tr>
    <?php endforeach; ?>
        </table>
        <table class="table table-condensed table-bordered">
          <tr>
            <td>Amount for month</td><td><?php echo mf($data['budget_data']['amount']); ?></td>
          </tr><tr>
            <td>Budgeted</td><td><?php echo mf($sum); ?></td>
          </tr><tr>
            <td>Diff</td><td><?php echo mf($data['budget_data']['amount'] - $sum); ?></td>
          </tr><tr>
            <td>Spent in budgets</td><td><?php echo mf($spent); ?></td>
          </tr><tr>
            <td>Spent outside budgets</td><td><?php echo mf($data['budget_data']['spent_outside']); ?></td>
          </tr><tr>
            <td>Left</td><td><?php echo mf($data['budget_data']['amount'] - $data['budget_data']['spent_outside'] - $spent); ?></td>
          </tr>
        </table>
  <?php endif; ?>
    </div>
<?php endif; ?>
</div>
<?php if (count($data['targets']) > 0) { ?>
  <div class="row-fluid">
    <div class="span6">
      <h4>Saving targets</h4>
      <table class="table table-striped table-condensed">
  <?php foreach ($data['targets'] as $target) { ?>
          <tr>
            <th style="width:30%;"><?php echo HTML::Link('home/target/overview/' . $target['id'], $target['description']); ?></th>
            <td><small><?php echo mf($target['saved']); ?> ... <?php echo mf($target['amount']); ?> </td>
          <tr>
            <td colspan="3">
              <div class="targetOverviewGraph loading" data-value="<?php echo $target['id']; ?>" id="targetOverviewGraph<?php echo $target['id']; ?>"></div>
            </td>
  <?php } ?>
      </table>
    </div>
  </div>
<?php } ?>


<?php
$now   = new DateTime('now');
$first = BaseController::getFirst();
$diff  = $first->diff($now);
if ($diff->m > 1) {
  ?>
  <div class="row-fluid">
    <div class="span12">
      <h4>Overspending</h4>
      <div id="ovcat"><em>You're doing fine!</em></div>
    </div>
  </div>
<?php } ?>
<script src="/js/home.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>