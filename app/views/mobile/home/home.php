<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <?php
    if (count($data['accounts']) > 0) {
      echo '<h2>Accounts</h2>';
      foreach ($data['accounts'] as $account):
        ?>
        <h3><?php echo HTML::Link('/home/account/overview/' . $account['id'], $account['name']); ?></h3>
        <?php
        echo '<p>Balance: ' . mf($account['currentbalance']) . '</p>';
        ?>
        <?php if (count($account['list']) > 0): ?>
          <table class="table table-bordered">
            <?php
            foreach ($account['list'] as $list) {
              foreach ($list as $t) {
                ?>
                <tr>
                  <td><?php echo $t['date']->format('d F'); ?></td>

                  <?php if ($t['type'] == 'Transaction'): ?>
                    <td><?php echo HTML::Link('/home/transaction/edit/' . $t['id'], $t['description']); ?></td>
                    <td>
                      <?php if (!is_null($t['category_id'])): ?>
                        <?php echo HTML::Link('/home/category/overview/' . $t['category_id'], $t['category_name']); ?>

                      <?php endif; ?>
                    </td>
                  <?php else: ?>
                    <td><?php echo HTML::Link('/home/transfer/edit/' . $t['id'], $t['description']); ?></td>
                    <td>
                      <?php if ($t['type'] == 'Transfer' && $t['account_from'] == $account['id']): ?>
                        &rarr;
                        <?php echo HTML::Link('/home/account/overview/' . $t['account_to'], $t['account_to_name']); ?>
                      <?php elseif ($t['type'] == 'Transfer' && $t['account_to'] == $account['id']): ?>
                        &larr;
                        <?php echo HTML::Link('/home/account/overview/' . $t['account_from'], $t['account_from_name']); ?>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                  <td>
                    <?php if ($t['type'] == 'Transfer' && $t['account_from'] == $account['id']): ?>
                      <?php echo mf($t['amount'] * -1); ?>
                    <?php else: ?>
                      <?php echo mf($t['amount']); ?>
                    <?php endif; ?>
                  </td>
                  <?php
                }
              }
              ?>
          </table>
        <?php endif; ?>

      <?php endforeach;
      ?>

      <p><strong>Total: <?php echo mf($data['acc_data']['sum']); ?></strong></p>
    <?php } ?>
  </div>
</div>
<div class="row-fluid">
  <?php if (count($data['accounts']) > 0): ?>
    <div class="span12">
      <h3>Budgets</h3>
      <?php
      $sum   = 0;
      $spent = 0;
      foreach ($data['budgets'] as $budget): $sum += $budget['amount'];
        $spent += $budget['spent'];
        ?>
        <h3><a href="/home/budget/overview/<?php echo $budget['id']; ?>"><?php echo $budget['name']; ?></a></h3>
        <p>Amount: <?php echo mf($budget['amount']); ?> / Spent: <?php echo mf($budget['spent']); ?> / Left: <?php echo mf($budget['left']); ?></p>
        <?php if (count($budget['list']) > 0): ?>
          <table class="table table-bordered">
            <?php foreach ($budget['list'] as $list): ?>

              <?php foreach ($list as $t): ?>
                <tr>
                  <td><?php echo $t['date']->format('d F'); ?></td>
                  <?php if ($t['type'] == 'Transaction'): ?>
                    <td><?php echo HTML::Link('/home/transaction/edit/' . $t['id'], $t['description']); ?></td>
                    <td>
                      <?php if (!is_null($t['category_id'])): ?>
                        <?php echo HTML::Link('/home/category/overview/' . $t['category_id'], $t['category_name']); ?>
                      <?php endif; ?>
                    </td>
                  <?php else: ?>
                    <td><?php echo HTML::Link('/home/transfer/edit/' . $t['id'], $t['description']); ?></td>
                    <td>&nbsp;</td>
                    <?php if ($t['type'] == 'Transfer' && $t['account_from'] == $account['id']): ?>
                      <td>&rarr; <?php echo HTML::Link('/home/account/overview/' . $t['account_to'], $t['account_to_name']); ?></td>
                    <?php elseif ($t['type'] == 'Transfer' && $t['account_to'] == $account['id']): ?>
                      <td>&larr; <?php echo HTML::Link('/home/account/overview/' . $t['account_from'], $t['account_from_name']); ?></td>
                    <?php endif; ?>
                  <?php endif; ?>
                  <td>
                    <?php if ($t['type'] == 'Transfer' && $t['account_from'] == $account['id']): ?>
                      <?php echo mf($t['amount'] * -1); ?>
                    <?php else: ?>
                      <?php echo mf($t['amount']); ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>

          </table>
        <?php endif; ?>
      <?php endforeach; ?>
      <h2>Summary</h2>
      <p>
        Amount for month: <?php echo mf($data['budget_data']['amount']); ?><br />
        Budgeted: <?php echo mf($sum); ?><br />
        Diff: <?php echo mf($data['budget_data']['amount'] - $sum); ?><br />
        <br />
        Spent in budgets: <?php echo mf($spent); ?><br />
        Spent outside budgets: <?php echo mf($data['budget_data']['spent_outside']); ?><br />
        Left: <?php echo mf($data['budget_data']['amount'] - $data['budget_data']['spent_outside'] - $spent); ?><br />
      <?php endif; ?>
    </p>
  </div>
</div>

<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>