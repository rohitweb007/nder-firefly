<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span2"></div>
  <div class="span8">
    <h3>All your beneficiaries</h3>
      <table class="table table-bordered table-condensed table-striped">
        <tr>
          <th>Name</th>
          <th>Current month</th>
          <th>&nbsp;</th>
        </tr>
        <?php foreach($beneficiaries as $b) : ?>
        <tr>
          <td><?php echo HTML::link('/home/beneficiary/overview/'.$b['id'],$b['name']);?></td>
          <td><?php
            if($b['month'] == 0) {
              $class = 'muted';
            } else if($b['month'] < 0) {
              $class = 'text-warning';
            } else {
              $class = 'text-success';
            }
            echo '<span class="'.$class.'">'.mf($b['month']).'</span>';
            ?>
          </td>
          <td>
            <a href="/home/beneficiary/edit/<?php echo $b['id'];?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#" data-value="<?php echo $b['id']; ?>" title="Delete <?php echo $b['name'];?>" class="btn btn-danger deleteBeneficiary"><i data-value="<?php echo $b['id']; ?>" class="icon-white icon-remove"></i></a>
          </td>
        </tr>

        <?php endforeach; ?>
      </table>
  </div>
  <div class="span2"></div>
</div>

<div id="modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
    <p>
      Are you sure you want to delete  "<span id="delBeneficiaryName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url'   => '/home/beneficiary/delete', 'style' => 'display:inline;', 'id'    => 'delBeneficiaryForm')); ?>

    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/beneficiary.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>