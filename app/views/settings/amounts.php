<?php require_once(__DIR__ . '/../layouts/top.php') ?>

<div class="row-fluid">
  <div class="span6">
    <h2>Amounts</h2>
    <p>
      Each month starts with a certain amount of money. This can be your
      salary or any other (combined) income. On this page you can set a
      <em>default</em> amount of money and specify it for each month that
      has a different start. For example, you could increase it for December
      to account for gifts and presents.
    </p>
  </div>
</div>

<div class="row-fluid">
  <div class="span6">
    <h4>Default amount</h4>

    <?php echo Form::open(array('class' => 'form-inline', 'url'   => '/home/settings/update')); ?>
    <input type="hidden" name="special_redirect" value="amounts" />&nbsp;
    <?php echo Form::input('number', 'defaultAmount', $defaultAmount, array('id'           => 'inputDefaultamount', 'autocomplete' => 'off', 'class'        => 'input', 'placeholder'  => $defaultAmount)); ?>&nbsp;
    <button type="submit" class="btn">Save</button>
    <?php echo Form::close(); ?>
  </div>
</div>

<div class="row-fluid">
  <div class="span6">
    <h4>Amounts for specific months</h4>

    <table class="table table-striped">
      <?php foreach ($settings as $s): ?>
        <tr id="setting_<?php echo $s->id; ?>" data-datetext="<?php echo $dates[$s->date]; ?>" data-datevalue="<?php echo $s->date; ?>">
          <td><?php echo $dates[$s->date];
      unset($dates[$s->date]); ?></td>
          <td><?php echo mf(intval(Crypt::decrypt($s->value))); ?></td>
          <td><a href="#" class="btn btn-danger deleteSetting" data-value="<?php echo $s->id; ?>"><i data-value="<?php echo $s->id; ?>" class="icon-trash icon-white"</a></td>
        </tr>
<?php endforeach; ?>
    </table>


    <h4>Add an amount</h4>
    <?php
    echo Form::open(array('class' => 'form-inline', 'url'   => '/home/settings/add'));
    echo Form::hidden('name', 'monthlyAmount');
    echo Form::select('date', $dates, date('Y-m-') . '01',array('id' => 'newDateSelect'));
    echo '<input type="hidden" name="special_redirect" value="amounts" />&nbsp;';
    echo Form::input('number', 'value', null, array('autocomplete' => 'off', 'class'        => 'input-small', 'placeholder'  => '&euro;'));
    echo '&nbsp;<button type="submit" class="btn">Save</button>';
    echo '<br /><span class="text-error">' . $errors->first('name') . '</span>';
    echo '<br /><span class="text-error">' . $errors->first('value') . '</span>';
    echo '<br /><span class="text-error">' . $errors->first('date') . '</span>';
    echo Form::close();
    ?>

  </div>

</div>



<script src="/js/amounts.js"></script>
<?php require_once(__DIR__ . '/../layouts/bottom.php') ?>