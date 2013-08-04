<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $object->id; ?>");
  var Name = "<?php echo Crypt::decrypt($object->name); ?>";
  var object = "<?php echo $name;?>";
  var objects = "<?php echo $names;?>";
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Overview for <?php echo Crypt::decrypt($object->name); ?> <span id="date"></span></h3>
    <a href="/home/<?php echo $name;?>/edit/<?php echo $object->id; ?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo Crypt::decrypt($object->name); ?></a>
    <a href="#" data-value="<?php echo $object->id; ?>" title="Delete <?php echo Crypt::decrypt($object->name); ?>" class="btn btn-danger deleteObject"><i data-value="<?php echo $object->id; ?>" class="icon-white icon-remove"></i> Delete <?php echo Crypt::decrypt($object->name); ?></a>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="dashboard"></div>
    <div id="chart"></div>
    <div id="control"></div>
  </div>
</div>
<div class="row-fluid">
  <div class="span4"><h4>Expenses per budget</h4><div id="budgetsexpenses" class="loading"></div></div>
  <div class="span4"><h4>Expenses per beneficiary</h4><div id="beneficiariesexpenses" class="loading"></div></div>
  <div class="span4"><h4>Expenses per category</h4><div id="categoriesexpenses"></div></div>
</div>
<div class="row-fluid">
  <div class="span4"><h4>Incomes per budget</h4><div id="budgetsincome" class="loading"></div></div>
  <div class="span4"><h4>Incomes per beneficiary</h4><div id="beneficiariesincome" class="loading"></div></div>
  <div class="span4"><h4>Incomes per category</h4><div id="categoriesincome"></div></div>
</div>

<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete "<span id="delObjectName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url'   => '/home/'.$name.'/delete', 'style' => 'display:inline;', 'id'    => 'delObjectForm')); ?>
    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>

<script src="/js/overview.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>