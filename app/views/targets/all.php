<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h3>All your saving targets</h3>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <table class="table table-striped">
      <tr>
        <th>Description</th>
        <th>Target amount</th>
        <th>Current amount so far</th>
        <th>Should have saved</th>
        <th>Daily / weekly / monthly guide</th>
        <th>Start date</th>
        <th>Due date</th>
        <th>&nbsp;</th>
      </tr>
      <?php foreach($data as $t): ?>
      <tr
        <?php if($t['closed']): ?>
        class="closed_target"
        <?php endif; ?>
        >
        <td><?php echo HTML::Link('/home/targets/overview/' . $t['id'],$t['description']);?></td>
        <td><?php echo $t['amount'];?></td>

        <td><?php echo $t['current'];?></td>
        <td><?php echo $t['should'];?></td>
        <td><?php echo $t['daily'];?> / <?php echo $t['weekly'];?> / <?php echo $t['monthly'];?></td>
        <td><?php echo $t['start'];?></td>
        <td><?php echo $t['due'];?></td>
        <td>
          <a href="/home/target/edit/<?php echo $t['id'];?>" class="btn"><i class="icon-pencil"></i></a>
          <a href="#" data-value="<?php echo $t['id']; ?>" title="Delete <?php echo $t['description'];?>" class="btn btn-danger deleteTarget"><i data-value="<?php echo $t['id']; ?>" class="icon-white icon-remove"></i></a>
        </td>
      </tr>
      <?php endforeach;?>
    </table>
  </div>
</div>




<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete "<span id="delTargetName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url' => '/home/target/delete','style' => 'display:inline;','id' => 'delTargetForm')); ?>
    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/target.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>