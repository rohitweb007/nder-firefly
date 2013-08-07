<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span8">
    <h3>Predicted expenses</h3>
    <p>
      This chart tries to predict what your balance is going to be. It starts with this months
      opening balance (see also <?php echo HTML::Link('/home/amounts', 'Amounts'); ?>) and works
      from there. It does not include incomes (it's down hill from here!).
    </p>
    <p>
      
    </p>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="predictionChart"></div>
  </div>
</div>


<script src="/js/prediction.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>