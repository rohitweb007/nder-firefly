<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h3>Progression so far</h3>
  </div>
</div>

<div class="row-fluid">
  <h3>Budgets</h3>
</div>

<div class="row-fluid">
<?php $index = 1; ?>
<?php foreach ($budgets as $budget => $count): ?>
  <div class="span4">
    <h4><?php echo $budget;?></h4>
    <div id="budget_<?php echo Str::slug($budget);?>" data-value="<?php echo $budget;?>" class="budgetProgressChart"></div>
  </div>




    <!-- We place three budgets next to each other. So if index %3 == 0, end the new block:   -->
    <?php if ($index % 3 == 0): ?>
    </div>
    <div class="row-fluid">
  <?php endif; ?>

  <?php
  $index++;
endforeach;
?>
    </div>
<script src="/js/progress.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>