<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span2"></div>
  <div class="span10">
    <h2>Import data</h2>
    <p>
      This form allows you to re-import old data. Everything else will
      be deleted!</p>
    <?php echo Form::open(array('files' => true));
      echo Form::file('payload');
      ?>
    <br />Or paste here<br />
    <?
      echo Form::textarea('payload_text');
      echo '<br />';
      echo Form::submit('Upload',array('class' => 'btn'));
    echo Form::close(); ?>
  </div>
</div>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>