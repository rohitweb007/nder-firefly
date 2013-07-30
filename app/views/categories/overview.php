<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $category->id; ?>");
  var Name = "<?php echo Crypt::decrypt($category->name);?>";
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Overview for <?php echo Crypt::decrypt($category->name); ?> <span id="date"></span></h3>
    <a href="/home/category/edit/<?php echo $category->id; ?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo Crypt::decrypt($category->name); ?></a>
    <a href="#" data-value="<?php echo $category->id; ?>" title="Delete <?php echo Crypt::decrypt($category->name);?>" class="btn btn-danger deleteBeneficiary"><i data-value="<?php echo $category->id; ?>" class="icon-white icon-remove"></i> Delete <?php echo Crypt::decrypt($category->name);?></a>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="categoryDashboard"></div>
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
      <li><a href="#beneficiaries" data-toggle="tab">Beneficiaries</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" id="summary">
        <div id="summaryText"></div>
      </div>
      <div class="tab-pane" id="transactions">
        <div id="transactionsTable"></div>
      </div>
      <div class="tab-pane" id="budgets">
        <div id="budgetTable"></div>
      </div>
      <div class="tab-pane" id="beneficiaries">
        <div id="beneficiaryTable"></div>
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
      Are you sure you want to delete "<span id="delCategoryName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url' => '/home/category/delete','style' => 'display:inline;','id' => 'delCategoryForm')); ?>
    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>

  <script src="/js/category.js"></script>
  <?php require_once(__DIR__ . '/../layouts/bottom.php') ?>