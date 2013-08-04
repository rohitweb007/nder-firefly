<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h3>All your accounts</h3>
  </div>
</div>

<div class="row-fluid">
  <div class="span1"></div>
  <div class="span10">
    <div id="allChart" class="loading" style="height:300px;margin-bottom:40px;"></div>
  </div>
  <div class="span1"></div>
</div>

<div class="row-fluid">
  <div class="span1"></div>
  <div class="span10">
    <table class="table table-striped table-bordered">
      <tr>
        <th>Account name</th>
        <th>Current balance</th>
        <th colspan="2">&nbsp;</th>
      </tr>
      <?php foreach($data as $account): ?>
      <tr>
        <td><?php echo HTML::Link('/home/account/overview/' . $account['id'],$account['name']); ?></td>
        <td>
          <?php if($account['balance'] > 0): ?>
            <span class="text-success"><?php echo mf($account['balance']); ?></span>
          <?php else: ?>
            <span class="text-error"><?php echo mf($account['balance']); ?></span>
          <?php endif; ?>
        </td>
        <td>
          <a href="/home/account/edit/<?php echo $account['id'];?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo $account['name'];?></a>
        </td><td>
          <a href="#" data-value="<?php echo $account['id']; ?>" title="Delete <?php echo $account['name'];?>" class="btn btn-danger deleteAccount"><i data-value="<?php echo $account['id']; ?>" class="icon-white icon-remove"></i> Delete <?php echo $account['name'];?></a>
        </td>
      </tr>

      <?php endforeach; ?>
    </table>
  </div>
  <div class="span1"></div>

</div>


<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete "<span id="delAccountName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url' => '/home/account/delete','style' => 'display:inline;','id' => 'delAccountForm')); ?>
    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/account.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>