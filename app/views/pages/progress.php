<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h3>Progression so far</h3>
  </div>
</div>

<div class="row-fluid">
  <h3>Budgets</h3>
</div>

<script type="text/javascript">
var cached = new Array();
</script>

<div class="row-fluid">
<?php $index = 1; ?>
<?php foreach ($budgets as $budget => $data): ?>
  <div class="span4">
    <h4><?php echo $budget;?></h4>

    <script type="text/javascript">
      cached["<?php echo $budget;?>"] = <?php echo json_encode(Cache::get(cacheKey('budgetProgress', $budget, Session::get('period'))));?>
    </script>

    <div id="budget_<?php echo Str::slug($budget);?>" data-value="<?php echo $budget;?>" class="loading budgetProgressChart"></div>
    <table class="table table-condensed">
      <tr>
        <td><?php echo mf($data['spent']);?></td>
        <td style="text-align:center;"><?php echo mf($data['avg']);?></td>
        <td style="text-align:right;"><?php echo mf(0);?></td>
      </tr>
    </table>
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