<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">

    <?php
    if (count($data['accounts']) > 0) {
      echo '<h4>Accounts</h4><table class="table table-condensed table-striped">';
      foreach ($data['accounts'] as $account):
        ?>
        <tr>
          <th style="width:30%;">
            <?php
            echo HTML::Link('/home/account/overview/' . $account['id'], $account['name'], $account['header']);
            if ($account['maxpct'] < 30 && $account['minpct'] < 30) {
              echo '&nbsp;&nbsp;&nbsp;<small style="font-weight:normal;">' . mf($account['currentbalance']) . '</small>';
            }
            ?>
          </th>
          <td style="width:35%;border-bottom:1px #ddd solid;">
            <?php
            if ($account['minpct'] > 0) {
              echo '<div style="margin:0;" class="progress progress-striped"><div class="bar bar-danger" style="width:' . $account['minpct'] . '%;display: block; float: right;text-align:right;">';

              if($account['minpct'] > 10) {
                echo '<small>'. mf($account['currentbalance']) . '&nbsp;</small>';
              }

              echo '</div></div>';
            }
            ?>
          </td>
          <td style="width:30%;border-bottom:1px #ddd solid;">
            <?php if ($account['maxpct'] > 0) : ?>
              <div style="margin:0;" class="progress progress-striped"><div class="bar bar-success" style="width:<?php echo $account['maxpct']; ?>%;text-align:left;">
                  <?php
                  if ($account['maxpct']> 30) {
                    echo '<small>&nbsp;' . mf($account['currentbalance']).'</small>';
                  }
                  ?>
                </div></div>
            <?php endif; ?>
          </td>
          <td style="width:5%;">
            <?php
            if (count($account['list']) > 0) {
              echo '<i data-value="Account' . $account['id'] . '" class="showTransactions icon-folder-close"></i>';
            }
            ?>
          </td>
        </tr>
        <tr>
        </tr>
        <tr style="display:none;"><td colspan="2"></td></tr>
        <tr>
          <td colspan="4">
            <div class="accountOverviewGraph loading" data-value="<?php echo $account['id']; ?>" id="accountOverviewGraph<?php echo $account['id']; ?>"></div>
          </td>
        </tr>
        <?php if (count($account['list']) > 0): ?>
          <tr style="display:none;"><td colspan="2"></td></tr>
          <tr>
            <td style="border-top:0;" colspan="4">
              <table class="table table-condensed table-bordered" class="fade in" style="display:none;" id="Account<?php echo $account['id']; ?>Table">
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
                        <?php if ($t['type'] == 'Transfer' && $t['account_from'] == $account['id']): ?>
                          <td>&rarr;
                            <?php echo HTML::Link('/home/account/overview/' . $t['account_to'], $t['account_to_name']); ?></td>
                        <?php elseif ($t['type'] == 'Transfer' && $t['account_to'] == $account['id']): ?>
                          <td>&larr;
                            <?php echo HTML::Link('/home/account/overview/' . $t['account_from'], $t['account_from_name']); ?></td>
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
                    <?php
                  }
                }
                ?>

              </table>
            </td>
          </tr>
        <?php endif; ?>

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
          <?php $sum = 0;$spent=0; foreach ($data['budgets'] as $budget): $sum += $budget['amount']; $spent += $budget['spent'];?>

            <tr>
              <th style="width:30%;">
                <?php if ($budget['overflow'] === false): ?>
                  <a href="/home/budget/overview/<?php echo $budget['id']; ?>"><?php echo $budget['name']; ?></a>
                <?php else: ?>
                  <a href="/home/budget/overview/<?php echo $budget['id']; ?>" class="tt" title="Firefly predicts you will overspend on this budget!"><?php echo $budget['name']; ?></a>
                <?php endif; ?>
              </th>
              <td style="width:65%;">
                <?php if ($budget['amount'] > 0 && $budget['amount'] >= $budget['spent']): ?>
                  <div style="margin:0;" class="progress progress-striped"><div class="bar<?php if ($budget['overflow'] && $budget['widthpct'] < 100): ?> bar-warning<?php else: ?> bar-success<?php endif; ?>" style="width:<?php echo ($budget['widthpct'] < 100 ? $budget['widthpct'] : 100); ?>%;text-align:left;">
                    <?php if ($budget['widthpct'] > 5): ?>
                        <small>&nbsp;<?php echo mf($budget['spent']); ?></small>
                    <?php endif; ?>
                    </div>
                    <?php if(100-$budget['widthpct'] > 10): ?>
                    <small>&nbsp;<?php echo mf($budget['left']);?></small>
                    <?php endif; ?>
                    </div>
                <?php elseif ($budget['amount'] > 0 && $budget['widthpct'] > 100): ?>
                  <?php $orangePCT = (100 / $budget['widthpct']) * 100; ?>
                  <div style="margin:0;" class="progress progress-striped">
                    <div class="bar bar-warning" style="text-align:left;width:<?php echo $orangePCT; ?>%"><small>&nbsp;<?php echo mf($budget['spent']); ?></small></div>
                    <div class="bar bar-danger" style="width:<?php echo 100 - $orangePCT; ?>%"></div>
                  </div>

                <?php endif; ?>
              </td>
              <td style="width:5%">
                <?php if (count($budget['list']) > 0): ?>
                  <i data-value="Budget<?php echo $budget['id']; ?>" class="showTransactions icon-folder-close"></i>
                <?php endif; ?>
              </td>
            </tr>
            <tr>

            </tr>
            <tr style="display:none;"><td colspan="3"></td></tr>
            <tr>
              <td colspan="3">
                <div class="budgetOverviewGraph loading" data-value="<?php echo $budget['id']; ?>" id="budgetOverviewGraph<?php echo $budget['id']; ?>"></div>
              </td>
            </tr>
            <?php if (count($budget['list']) > 0): ?>
              <tr style="display:none;"><td colspan="2"></td></tr>
              <tr>
                <td style="border-top:0;" colspan="4">
                  <table class="table table-condensed table-bordered" class="fade in" style="display:none;" id="Budget<?php echo $budget['id']; ?>Table">
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
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </table>
        <p>
          Amount for month: <?php echo mf($data['budget_data']['amount']); ?><br />
          Budgeted: <?php echo mf($sum); ?><br />
          Diff: <?php echo mf($data['budget_data']['amount'] - $sum); ?><br />
          <br />
          Spent in budgets: <?php echo mf($spent); ?><br />
          Spent outside budgets: <?php echo mf($data['budget_data']['spent_outside']); ?><br />
          Left: <?php echo mf($data['budget_data']['amount'] - $data['budget_data']['spent_outside'] - $spent); ?><br />
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
            <?php if ($target['pct'] != null) { ?>
              <td style="width:50%">
                <div style="margin:0;" class="progress progress-striped"><div class="bar bar-success" style="width:<?php echo $target['pct']; ?>%;text-align:left;">
                    <?php if ($target['pct'] > 15) { ?>
                      &nbsp;<?php echo mf($target['saved']); ?>
                    <?php } ?>
                  </div></div>
              </td>
            <?php } else { ?>
              <td style="width:70%"></td>
            <?php } ?>
        <!--<td style="width:40%;"><small>
            <?php echo mf($target['saved']); ?> /
            <?php echo mf($target['amount']); ?> /
            <?php echo mf($target['should']); ?>

          </small></td>-->
          </tr>
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