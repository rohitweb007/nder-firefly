<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span2"></div>
  <div class="span8">
    <h3>All your categories</h3>
      <table class="table table-bordered table-condensed table-striped">
        <tr>
          <th>Name</th>
          <th>Current month</th>
          <th>&nbsp;</th>
        </tr>
        <?php foreach($categories as $c) : ?>
        <tr>
          <td><?php echo HTML::link('/home/category/overview/'.$c['id'],$c['name']);?></td>
          <td><?php
            if($c['month'] == 0) {
              $class = 'muted';
            } else if($c['month'] < 0) {
              $class = 'text-warning';
            } else {
              $class = 'text-success';
            }
            echo '<span class="'.$class.'">'.mf($c['month']).'</span>';
            ?>
          </td>
          <td>
            <a href="/home/category/edit/<?php echo $c['id'];?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#" data-value="<?php echo $c['id']; ?>" title="Delete <?php echo $c['name'];?>" class="btn btn-danger deleteCategory"><i data-value="<?php echo $c['id']; ?>" class="icon-white icon-remove"></i></a>
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
      Are you sure you want to delete  "<span id="delCategoryName"></span>"? You cannot undo this!
    </p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo Form::open(array('url'   => '/home/category/delete', 'style' => 'display:inline;', 'id'    => 'delCategoryForm')); ?>

    <button class="btn btn-danger">Delete it!</button>
    <?php echo Form::close(); ?>
  </div>
</div>


<script src="/js/category.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>