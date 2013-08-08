<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $target->id; ?>");
  var Name = "<?php echo Crypt::decrypt($target->description); ?>";
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Overview for <?php echo Crypt::decrypt($target->description); ?></h3>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <div id="chart" class="loading" style="height:300px;"></div>
  </div>
</div>

<div class="row-fluid">
  <div class="span4">
    <table class="table table-bordered table-striped">
      <tr>
        <td>Saved so far</td>
        <td><?php echo mf($data['info']['saved']);?> (<?php echo $data['info']['saved_pct'];?>%)</td>
      </tr>
      <tr>
        <td>Should have saved</td>
        <td><?php echo mf($data['info']['should']);?></td>
      </tr>
      <tr>
        <td>Daily guide</td>
        <td><?php echo mf($data['info']['daily']);?></td>
      </tr>
      <tr>
        <td>Weekly guide</td>
        <td><?php echo mf($data['info']['weekly']);?></td>
      </tr>
      <tr>
        <td>Monthly guide</td>
        <td><?php echo mf($data['info']['monthly']);?></td>
      </tr>
    </table>
  </div>
</div>

<div class="row-fluid">
  <div class="span1"></div>
  <div class="span10">
    <table class="table table-bordered table-striped">
      <tr>
        <th>Description</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Accounts</th>
        <th>Category</th>
        <th>Budget</th>
        <th>&nbsp;</th>
      </tr>
      <?php foreach ($data['transfers'] as $t): ?>
        <tr>
          <td>
            <?php
            if ($t['ignoreprediction']) {
              echo '<i class="icon-eye-close" title="Ignore in predictions" alt="Ignore in predictions"></i> ';
            }
            if ($t['countasexpense']) {
              echo '<i class="icon-shopping-cart" title="Count as expense" alt="Count as expense"></i> ';
            }
            ?>
            <?php echo HTML::Link('/home/transfer/edit/' . $t['id'], $t['description']); ?></td>
          <td><?php echo $t['date']->format('d F Y'); ?></td>
          <td><?php echo mf($t['amount']); ?></td>
          <td>
            <?php echo HTML::Link('home/account/overview/' . $t['account_from'], $t['account_from_name']); ?>
            &rarr;
            <?php echo HTML::Link('home/account/overview/' . $t['account_to'], $t['account_to_name']); ?>
          </td>

          <td><?php echo!is_null($t['budget_id']) ? HTML::Link('/home/budget/overview/' . $t['budget_id'], $t['budget_name']) : ''; ?></td>
          <td><?php echo!is_null($t['category_id']) ? HTML::Link('/home/category/overview/' . $t['category_id'], $t['category_name']) : ''; ?></td>
          <td>
            <a href="/home/transfer/edit/<?php echo $t['id']; ?>" class="btn"><i class="icon-pencil"></i></a>
            <a href="#"  data-value="<?php echo $t['id']; ?>" title="Delete <?php echo $t['description']; ?>" class="btn btn-danger deleteTransfer"><i data-value="<?php echo $t['id']; ?>" class="icon-white icon-remove"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <div class="span1"></div>
</div>
<script src="/js/target.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>