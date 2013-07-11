<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span2">
    <h4>Options</h4>
    <p>Compare</p>
    <p>
      <?php echo Form::select('period', $months, (is_null($thisMonth) ? null : $thisMonth->format('Y-m-d')), array('class' => 'span10', 'id'    => 'baseCompare')); ?>
    </p>
    <p>
      With
    </p>
    <p>
      <?php echo Form::select('period', $months, (is_null($prevMonth) ? null : array($prevMonth->format('Y-m-d'))), array('multiple' => 'multiple', 'id'       => 'compareWith', 'class'    => 'span10')); ?>
    </p>
    <p>Account</p>
    <?php
    echo Form::select('account', $accounts, $account);
    ?>
    <p>
      <?php echo Form::submit('Compare', array('class' => 'btn', 'id'    => 'updateCompare')); ?>
    </p>



  </div>
  <div class="span10">
    <div class="row-fluid">
      <div class="span12">
        <h2>Comparision</h2>
        <p>
          This page lets you compare your expenses with other months. Select some options
          to the left and see what happens!
        </p>
      </div>
    </div>
    <div class="row-fluid" id="compareContent">
      <div class="span11 loading" id="basicChart"></div>
    </div>
    <div class="row-fluid">
      <div class="span6 loading">
        <h4>Totals</h4>
        <div id="basicTable"></div>
      </div>
      <div class="span5 loading">
        <h4>Budgets</h4>
        <div id="budgets"></div>
      </div>
    </div>
    <div class="row-fluid">
      <div class="span6 loading">
        <h4>Categories</h4>
        <div id="categories"></div>
      </div>

    </div>
  </div>
</div>
<script src="/js/compare.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>