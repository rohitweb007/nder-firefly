<?php
$currentPeriod = Session::get('period');
$periodName    = $currentPeriod->format('F Y');

// previous period:
$previous = clone $currentPeriod;
$previous->sub(new DateInterval('P1M'));
if ($previous->format('m') == $currentPeriod->format('m')) {
  $previous->sub(new DateInterval('P5D'));
}

$next = clone $currentPeriod;
$next->add(new DateInterval('P1M'));
if (intval($next->format('m')) == intval($currentPeriod->format('m')) + 2) {
  $next->sub(new DateInterval('P5D'));
}
?>


<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="/">Firefly / <?php echo $periodName; ?></a>
    <ul class="nav" role="menu">
      <li role="presentation" class="divider-vertical"></li>
      <li role="menuitem"><a tabindex="-1" href="/home/transaction/add"><i class="icon-plus"></i> New transaction</a></li>
    </ul>
  </div>
</div>