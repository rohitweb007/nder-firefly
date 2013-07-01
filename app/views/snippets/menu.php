<?php

$currentPeriod = Session::get('period');
$periodName    = $currentPeriod->format('F Y');

// previous period:
$previous = clone $currentPeriod;
$previous->sub(new DateInterval('P1M'));
if($previous->format('m') == $currentPeriod->format('m')) {
  $previous->sub(new DateInterval('P5D'));
}

$next = clone $currentPeriod;
$next->add(new DateInterval('P1M'));
if(intval($next->format('m')) == intval($currentPeriod->format('m'))+2  ) {
  $next->sub(new DateInterval('P5D'));
}

?>


<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="/">Firefly / <?php echo $periodName;?></a>
    <ul class="nav">
      <li class="divider-vertical"></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Navigation <b class="caret"></b></a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="drop-1">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/<?php echo strtolower($previous->format('Y/F')); ?>">&larr; <?php echo $previous->format('F Y'); ?></a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/<?php echo strtolower($next->format('Y/F')); ?>">&rarr; <?php echo $next->format('F Y'); ?></a></li>
        </ul>
      </li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Create <b class="caret"></b></a>

      <ul class="dropdown-menu" role="menu" aria-labelledby="drop-2">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/account/add"><i class="icon-plus"></i> New account</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/budget/add"><i class="icon-plus"></i> New budget</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/transaction/add"><i class="icon-plus"></i> New transaction</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/target/add"><i class="icon-plus"></i> New saving target</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/transfer/add"><i class="icon-plus"></i> New transfer</a></li>
        </ul>

      </li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Lists <b class="caret"></b></a>

      <ul class="dropdown-menu" role="menu" aria-labelledby="drop-3">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/accounts"><i class="icon-th-list"></i> Accounts</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/beneficiaries"><i class="icon-th-list"></i> Beneficiaries</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/budgets"><i class="icon-th-list"></i> Budgets</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/categories"><i class="icon-th-list"></i> Categories</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/transactions"><i class="icon-th-list"></i> Transactions</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/transfers"><i class="icon-th-list"></i> Transfers</a></li>
        </ul>

      </li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Manage <b class="caret"></b></a></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Graphs <b class="caret"></b></a></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Other <b class="caret"></b></a>

      <ul class="dropdown-menu" role="menu" aria-labelledby="drop-1">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/logout"><i class="icon-arrow-right"></i> Logout</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/delete"><i class="icon-warning-sign"></i> Logout and delete</a></li>
        </ul>

      </li>
    </ul>
  </div>
</div>