<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span6">
    <h4>Accounts</h4>
    <?php if (count($accounts) == 0): ?>
      <p><em>You have no accounts defined yet.</em></p>
      <p><em>Your first step should be to </em> <strong><?php echo HTML::Link('/home/account/add', 'add a new account'); ?></strong></p>
    <?php else: ?>
      <?php $sum = 0; ?>
      <table class="table table-condensed table-striped">
        <?php
        foreach ($accounts as $account):
          $sum += $account->balance();
          ?>
          <tr>
            <th style="width:30%;">
              <?php
              if ($account->currentbalance > 0) {
                echo HTML::Link('/home/account/overview/' . $account->id, $account->name);
              } else {
                echo HTML::Link('/home/account/overview/' . $account->id, $account->name, array('style' => 'color:red;', 'class' => 'tt', 'title' => $account->name . ' has a balance below zero. Try to fix this.'));
              }
              if ($account->maxpct < 30 && $account->minpct < 30) {
                echo '&nbsp;&nbsp;&nbsp;<small style="font-weight:normal;">' . mf($account->currentbalance) . '</small>';
              }
              ?>
            </th>
            <td style="width:35%;border-bottom:1px #ddd solid;">
              <?php
              if ($account->minpct > 0) {
                echo '<div style="margin:0;" class="progress progress-striped"><div class="bar bar-danger" style="width:' . $account->minpct . '%;display: block; float: right;text-align:right;">' . mf($account->currentbalance) . '&nbsp;</div></div>';
              }
              ?>
            </td>
            <td style="width:30%;border-bottom:1px #ddd solid;">
              <?php if ($account->maxpct > 0) : ?>
                <div style="margin:0;" class="progress progress-striped"><div class="bar bar-success" style="width:<?php echo $account->maxpct; ?>%;text-align:left;">
                    <?php
                    if ($account->maxpct > 30) {
                      echo '&nbsp;' . mf($account->currentbalance);
                    }
                    ?>
                  </div></div>
              <?php endif; ?>
            </td>
            <td style="width:5%;">
              <?php
              if (count($account->list) > 0) {
                echo '<i data-value="Account' . $account->id . '" class="showTransactions icon-folder-close"></i>';
              }
              ?>
            </td>
          </tr>
          <tr>
          </tr>
          <tr style="display:none;"><td colspan="2"></td></tr>
          <tr>
            <td colspan="4">
              <div class="accountOverviewGraph" data-value="<?php echo $account->id; ?>" id="accountOverviewGraph<?php echo $account->id; ?>"></div>
            </td>
          </tr>
          <?php if (count($account->list) > 0): ?>
            <tr style="display:none;"><td colspan="2"></td></tr>
            <tr>
              <td style="border-top:0;" colspan="4">
                <table class="table table-condensed table-bordered" class="fade in" style="display:none;" id="Account<?php echo $account->id; ?>Table">
                  <?php
                  foreach ($account->list as $list) {

                    foreach ($list as $t) {
                      ?>
                      <tr>
                        <td><?php echo date('d F', strtotime($t->date)); ?></td>
                        <?php if ($t instanceof Transaction): ?>
                          <td><?php echo HTML::Link('/home/transaction/edit/' . $t->id, Crypt::decrypt($t->description)); ?></td>
                          <td>
                            <?php if (!is_null($t->category_id)): ?>
                              <?php echo HTML::Link('/home/category/overview/' . $t->category()->first()->id, Crypt::decrypt($t->category()->first()->name)); ?>
                            <?php endif; ?>
                          </td>
                        <?php else: ?>
                          <td><?php echo HTML::Link('/home/transfer/edit/' . $t->id, Crypt::decrypt($t->description)); ?></td>
                          <?php if ($t instanceof Transfer && $t->account_from == $account->id): ?>
                            <td>&rarr;
                              <?php echo HTML::Link('/home/account/overview/' . $t->account_to, Crypt::decrypt($t->accountto()->first()->name)); ?></td>
                          <?php elseif ($t instanceof Transfer && $t->account_to == $account->id): ?>
                            <td>&larr;
                              <?php echo HTML::Link('/home/account/overview/' . $t->account_from, Crypt::decrypt($t->accountfrom()->first()->name)); ?></td>
                          <?php endif; ?>
                        <?php endif; ?>
                        <td>
                          <?php if ($t instanceof Transfer && $t->account_from == $account->id): ?>
                            <?php echo mf($t->amount * -1); ?>
                          <?php else: ?>
                            <?php echo mf($t->amount); ?>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php }
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
          <td><?php echo mf($sum); ?></td>
        </tr>
      </table>
<?php endif; ?>
  </div>

<?php if (count($accounts) > 0): ?>
    <div class="span6">
      <h4>Budgets</h4>
  <?php if (count($budgets) < 1): ?>
        <p>
          <em>Your next step should be to add a budget. A budget can help you accurately track expenses.</em><br />
        <?php echo HTML::link('/home/budget/add', 'Create a budget now.'); ?>
        </p>
        <?php else: ?>
        <table class="table table-condensed table-striped">
    <?php foreach ($budgets as $budget): ?>
            <tr>
              <th style="width:30%;">
                <?php if ($budget->overflow === false): ?>
                  <a href="/home/budget/overview/<?php echo $budget->id; ?>"><?php echo $budget->name; ?></a>
                <?php else: ?>
                  <a href="/home/budget/overview/<?php echo $budget->id; ?>" class="tt" title="Firefly predicts you will overspend on this budget!"><?php echo $budget->name; ?></a>
      <?php endif; ?>
              </th>
              <td style="width:65%;">
                  <?php if ($budget->amount > 0 && $budget->amount >= $budget->spent()): ?>
                  <div style="margin:0;" class="progress progress-striped"><div class="bar
                                                                                <?php if ($budget->overflow && $budget->widthpct < 100): ?>
                                                                                  bar-warning
                                                                                <?php else: ?>
                                                                                  bar-success
                                                                                <?php endif; ?>
                                                                                " style="width:<?php echo ($budget->widthpct < 100 ? $budget->widthpct : 100); ?>%;text-align:left;">
                      <?php if ($budget->widthpct > 5): ?>
                        &nbsp;<?php echo mf($budget->spent()); ?>
                  <?php endif; ?>
                    </div></div>
                <?php elseif($budget->amount > 0 && $budget->widthpct > 100): ?>
                <?php $orangePCT = (100 / $budget->widthpct)*100;  ?>
                <div style="margin:0;" class="progress progress-striped">
                  <div class="bar bar-danger" style="text-align:left;width:<?php echo $orangePCT; ?>%">&nbsp;<?php echo mf($budget->spent()); ?></div>
                  <div class="bar bar-warning" style="width:<?php echo 100-$orangePCT; ?>%"></div>
                </div>

      <?php endif; ?>
              </td>
              <td style="width:5%">
                <?php if(count($budget->list) > 0): ?>
                <i data-value="Budget<?php echo $budget->id; ?>" class="showTransactions icon-folder-close"></i>
                <?php endif;?>
              </td>
              <!--<td><small><span class="tt" title="The total amount of money in the budget."><?php echo mf($budget->amount); ?></span> / <span class="tt" title="The amount of money left"><?php echo mf($budget->left()); ?></span> / <span class="tt" title="The adviced spending per day."><?php echo mf($budget->advice()); ?></span> / <span class="tt" title="Expected expenses for this budget"><?php echo mf($budget->expected()); ?></span></small></td>-->
            </tr>
            <tr>

            </tr>
            <tr style="display:none;"><td colspan="3"></td></tr>
            <tr>
              <td colspan="3">
                <div class="budgetOverviewGraph" data-value="<?php echo $budget->id; ?>" id="budgetOverviewGraph<?php echo $budget->id; ?>"></div>
              </td>
            </tr>
      <?php if (count($budget->list) > 0): ?>
              <tr style="display:none;"><td colspan="2"></td></tr>
              <tr>
                <td style="border-top:0;" colspan="4">
                  <table class="table table-condensed table-bordered" class="fade in" style="display:none;" id="Budget<?php echo $budget->id; ?>Table">
                    <?php foreach ($budget->list as $list): ?>
          <?php foreach ($list as $t): ?>
                        <tr>
                          <td><?php echo date('d F', strtotime($t->date)); ?></td>
            <?php if ($t instanceof Transaction): ?>
                            <td><?php echo HTML::Link('/home/transaction/edit/' . $t->id, Crypt::decrypt($t->description)); ?></td>
                            <td>
                              <?php if (!is_null($t->category_id)): ?>
                                <?php echo HTML::Link('/home/category/overview/' . $t->category()->first()->id, Crypt::decrypt($t->category()->first()->name)); ?>
                            <?php endif; ?>
                            </td>
                          <?php else: ?>
                            <td><?php echo HTML::Link('/home/transfer/edit/' . $t->id, Crypt::decrypt($t->description)); ?></td>
                            <?php if ($t instanceof Transfer && $t->account_from == $account->id): ?>
                              <td>&rarr; <?php echo HTML::Link('/home/account/overview/' . $t->account_to, Crypt::decrypt($t->accountto()->first()->name)); ?></td>
                            <?php elseif ($t instanceof Transfer && $t->account_to == $account->id): ?>
                              <td>&larr; <?php echo HTML::Link('/home/account/overview/' . $t->account_from, Crypt::decrypt($t->accountfrom()->first()->name)); ?></td>
                            <?php endif; ?>
                            <?php endif; ?>
                          <td>
                            <?php if ($t instanceof Transfer && $t->account_from == $account->id): ?>
                              <?php echo mf($t->amount * -1); ?>
                            <?php else: ?>
                              <?php echo mf($t->amount); ?>
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
    <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<div class="row-fluid">
  <div class="span6">
    <h4>Saving targets</h4>
    <table class="table table-striped table-condensed">
    <?php foreach($targets as $target) { ?>
      <tr>
        <th style="width:30%;"><?php echo HTML::Link('home/target/overview/' . $target['id'],$target['description']); ?></th>
        <?php if($target['pct'] != null) { ?>
        <td style="width:50%">
          <div style="margin:0;" class="progress progress-striped"><div class="bar bar-success" style="width:<?php echo $target['pct']; ?>%;text-align:left;">
              <?php if($target['pct'] > 15) { ?>
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
          <div class="targetOverviewGraph" data-value="<?php echo $target['id']; ?>" id="targetOverviewGraph<?php echo $target['id']; ?>"></div>
        </td>
    <?php } ?>
    </table>
  </div>
</div>

<?php
$now   = new DateTime('now');
$first = BaseController::getFirst();
$diff  = $first->diff($now);
if ($diff->m > 1) {
  ?>
  <div class="row-fluid">
    <div class="span12">
      <h4>Overspending</h4>
      <div id="ovcat"></div>
    </div>
  </div>
<?php } ?>
<script src="/js/home.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>