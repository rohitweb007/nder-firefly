<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $beneficiary->id; ?>");
  var Name = "<?php echo Crypt::decrypt($beneficiary->name);?>";
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Overview for <?php echo Crypt::decrypt($beneficiary->name); ?> <span id="date"></span></h3>
    <a href="/home/beneficiary/edit/<?php echo $beneficiary->id; ?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo Crypt::decrypt($beneficiary->name); ?></a>
    <a href="#" data-value="<?php echo $beneficiary->id; ?>" title="Delete <?php echo Crypt::decrypt($beneficiary->name);?>" class="btn btn-danger deleteAccount"><i data-value="<?php echo $beneficiary->id; ?>" class="icon-white icon-remove"></i> Delete <?php echo Crypt::decrypt($beneficiary->name);?></a>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="beneficiaryDashboard"></div>
    <div id="chart"></div>
    <div id="control"></div>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <ul class="nav nav-tabs" id="tabs" data-tabs="tabs">
      <li class="active"><a href="#summary" data-toggle="tab">Summary</a></li>
      <li><a href="#transactions" data-toggle="tab">Transactions</a></li>
      <li><a href="#budgets" data-toggle="tab">Budgets</a></li>
      <li><a href="#categories" data-toggle="tab">Categories</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" id="summary">
        <div id="summaryTable">TABEL
        </div>
      </div>
      <div class="tab-pane" id="transactions">
        <div id="transactionsTable"></div>
      </div>
      <div class="tab-pane" id="budgets">
        <div id="budgetTable"></div>
      </div>
      <div class="tab-pane" id="categories">
        <div id="categoryTable"></div>
      </div>
    </div>

  </div>
</div>

<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete "<span id="delAccountName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url' => '/home/account/delete','style' => 'display:inline;','id' => 'delAccountForm')); ?>
    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>

  <script src="/js/beneficiary.js"></script>
  <?php require_once(__DIR__ . '/../layouts/bottom.php') ?>