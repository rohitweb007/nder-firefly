<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $beneficiary->id; ?>");
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Beneficiary <?php echo Crypt::decrypt($beneficiary->name);?> <span id="date"></span></h3>
    <a href="/home/account/edit/<?php echo $beneficiary->id; ?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo Crypt::decrypt($beneficiary->name); ?></a>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="beneficiaryDashboard"></div>
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

<script src="/js/beneficiary.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>