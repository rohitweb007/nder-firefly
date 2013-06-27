<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span12">

    <table class="table">
      <?php foreach ($transactions as $t) { ?>
        <tr>
          <td><?php echo $t->date; ?></td>
          <td><?php echo Crypt::decrypt($t->description); ?></td>
          <td><?php echo $t->amount; ?></td>
          <td>
            <?php if ($t->account_id != null) {
            echo Crypt::decrypt($t->account()->first()->name);
            }
            ?>
          </td>
          <td>
            <?php if ($t->budget_id != null) {
            echo Crypt::decrypt($t->budget()->first()->name);
            }
            ?>
          </td>
          <td>
            <?php if ($t->beneficiary_id != null) {
            echo Crypt::decrypt($t->beneficiary()->first()->name);
            }
            ?>
          </td>
          <td>
            <?php if ($t->category_id != null) {
            echo Crypt::decrypt($t->category()->first()->name);
            }
            ?>
          </td>



        </tr>

<?php } ?>
    </table>
  </div>
</div>

<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>