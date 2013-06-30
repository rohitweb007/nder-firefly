<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $account->id; ?>");
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Account <?php echo Crypt::decrypt($account->name);?> <span id="date"></span></h3>
    <a href="/home/account/edit/<?php echo $account->id; ?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo Crypt::decrypt($account->name); ?></a>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="accountDashboard"></div>
    <div id="chart"></div>
    <div id="control"></div>
  </div>
</div>
<div class="row-fluid" id="listDashboard">
  <div class="span4" id="budgetDashboard">
    <h4>Budgets</h4>
    <div id="budgetTable"></div>
    </div>
  <div class="span4"><h4>Categories</h4><div id="categoryTable"></div></div>
  <div class="span4"><h4>Moved</h4><div id="moveTable"></div></div>
</div>

<div class="row-fluid">
  <div class="span12">
    <h4>Transactions</h4>
    <div id="transactionsTable"></div>
</div>

<script src="/js/account.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>