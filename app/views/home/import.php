<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span2"></div>
  <div class="span10">
    <h3>Import data</h3>
    <p>
      This form allows you to re-import old data. Everything else will
      be deleted!</p>
    <?php echo Form::open(array('files' => true,'url' => $url));
      echo Form::file('payload');
      ?><br />
    <?php
      echo Form::submit('Upload',array('class' => 'btn'));
    echo Form::close(); ?>
  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>