<?php require_once(__DIR__ . '/../layouts/top.php') ?>
<div class="row-fluid">
  <div class="span12">
    <h2>All your accounts</h2>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="allChart"></div>
  </div>
</div>

<div clas="row-fluid">
  <div class="span12">
    <table class="table table-striped">
      <tr>
        <th>Account name</th>
        <th>Opening balance</th>
        <th>Current balance</th>
        <th>Average net per month</th>
        <th>&nbsp;</th>
      </tr>
      <?php foreach($data as $account): ?>
      <tr>
        <td><?php echo HTML::Link('/home/account/overview/' . $account['id'],$account['name']); ?></td>
        <td>
          <?php if($account['start'] > 0): ?>
            <span class="text-success" title="<?php echo $account['startdate']; ?>"><?php echo mf($account['start']); ?></span>
          <?php else: ?>
            <span class="text-error" title="<?php echo $account['startdate']; ?>"><?php echo mf($account['start']); ?></span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($account['current'] > 0): ?>
            <span class="text-success" title="<?php echo $account['currentdate']; ?>"><?php echo mf($account['current']); ?></span>
          <?php else: ?>
            <span class="text-error" title="<?php echo $account['currentdate']; ?>"><?php echo mf($account['current']); ?></span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($account['avg'] > 0): ?>
            <span class="text-success"><?php echo mf($account['avg']); ?></span>
          <?php else: ?>
            <span class="text-error"><?php echo mf($account['avg']); ?></span>
          <?php endif; ?>
        </td>
        <td>

          <a href="/home/account/edit/<?php echo $account['id'];?>" class="btn"><i class="icon-pencil"></i></a>
        </td>
      </tr>

      <?php endforeach; ?>
    </table>
  </div>

</div>

<script src="/js/account.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>