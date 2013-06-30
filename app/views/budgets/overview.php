<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<script>
  var ID = parseInt("<?php echo $budget->id; ?>");
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Budget <?php echo Crypt::decrypt($budget->name); ?> (<?php echo $budget->date; ?>)</h3>
    <a href="/home/budget/edit/<?php echo $budget->id; ?>" class="btn"><i class="icon-pencil"></i> Edit <?php echo Crypt::decrypt($budget->name); ?></a>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="budgetGraph">Graph here</div>
  </div>
</div>

<div class="row-fluid">
  <div class="span4">
    <h4>Overview</h4>
    <table class="table table-striped">
      <tr>
        <td>Total</td>
        <td><?php echo mf($budget->amount); ?></td>
      </tr>
      <tr>
        <td>Spent</td>
        <td><?php echo mf($budget->spent()); ?></td>
      </tr>
      <tr>
        <td>Left</td>
        <td><?php echo mf($budget->left()); ?></td>
      </tr>
      <tr>
        <td>Avg spent per day</td>
        <td><?php echo mf($budget->avgspent); ?></td>
      </tr>
      <tr>
        <td>Avg spending target</td>
        <td><?php echo mf($budget->spenttarget); ?></td>
      </tr>
    </table>
  </div>
  <div class="span4">
    <h4>Categories</h4>
    <table class="table table-striped">
      <tr>
        <th>Category</th>
        <th>Spent</th>
        <th>Spent percentage</th>
      </tr>
      <?php foreach($categories as $category): ?>
      <tr>
        <td><?php echo HTML::Link('/home/category/overview/'.$category['id'],$category['name']); ?></td>
        <td><?php echo mf($category['spent']); ?></td>
        <td><?php echo round(($category['spent'] / $budget->spent())*100,1); ?>%</td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <div class="span4">
    <h4>Beneficiaries</h4>
    <table class="table table-striped">
      <tr>
        <th>Beneficiary</th>
        <th>Spent</th>
        <th>Spent percentage</th>
      </tr>
      <?php foreach($beneficiaries as $beneficiary): ?>
      <tr>
        <td><?php echo HTML::Link('/home/beneficiary/overview/'.$beneficiary['id'],$beneficiary['name']); ?></td>
        <td><?php echo mf($beneficiary['spent']); ?></td>
        <td><?php echo round(($beneficiary['spent'] / $budget->spent())*100,1); ?>%</td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <h4>Transactions</h4>
    <table class="table table-striped">
      <tr>
        <th>Date</th>
        <th>Description</th>
        <th>Account</th>
        <th>Category</th>
        <th>Beneficiary</th>
        <th>Amount</th>
        <th>Total</th>
      </tr>
      <?php $total = $budget->spent();?>
      <?php foreach($budget->transactions()->orderBy('date','DESC')->get() as $t): ?>
      <tr>
        <td><?php echo date('d F',strtotime($t->date)); ?></td>
        <td><?php echo Crypt::decrypt($t->description); ?></td>
        <td>
          @if(!is_null($t->account_id))
            <?php echo Crypt::decrypt($t->account()->first()->name); ?>
          @endif
        </td>
        <td>
          @if(!is_null($t->category_id))
            <?php echo Crypt::decrypt($t->category()->first()->name); ?>
          @endif
        </td>
        <td>
          @if(!is_null($t->beneficiary_id))
            <?php echo Crypt::decrypt($t->beneficiary()->first()->name); ?>
          @endif
        </td>
        <td><?php echo mf($t->amount); ?></td>
        <td>
          <?php echo mf($total); ?>
          <?php $total += $t->amount; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<script src="/js/budget.js"></script>

<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>
