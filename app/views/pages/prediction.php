<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h3>Predicted balance</h3>
  </div>
</div>
<div class="row-fluid">
  <div class="span2">
    <h4>Options</h4>

    <h5>Show predictions for:</h5>
    <p>
      <?php foreach($accounts as $a): ?>
      <?php echo $a['name'];?><br />
      <?php endforeach; ?>
    </p>
    <h5>Or for budget</h5>
    
  </div>
  <div class="span10">

    <p>
      This chart tries to predict what your balance is going to be. It starts with this months
      opening balance (see also <?php echo HTML::Link('/home/amounts', 'Amounts'); ?>) and works
      from there. It does not include incomes (it's down hill from here!).
    </p>

    <div id="predictionChart"></div>
  </div>
</div>

<script src="/js/prediction.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>