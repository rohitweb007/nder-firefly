<?php

$currentPeriod = Session::get('period');
$periodName    = $currentPeriod->format('F Y');

// previous period:
$previous = clone $currentPeriod;
$previous->sub(new DateInterval('P1M'));

$next = clone $currentPeriod;
$next->add(new DateInterval('P1M'));

?>


<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="/">Firefly / {{$periodName}}</a>
    <ul class="nav">
      <li class="divider-vertical"></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Navigation <b class="caret"></b></a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="drop-1">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/{{strtolower($previous->format('Y/F'))}}">&larr; {{$previous->format('F Y')}}</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/{{strtolower($next->format('Y/F'))}}">&rarr; {{$next->format('F Y')}}</a></li>
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
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/categories"><i class="icon-th-list"></i> Categories</a></li>
        </ul>

      </li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Manage <b class="caret"></b></a></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Graphs <b class="caret"></b></a></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Other <b class="caret"></b></a>

      <ul class="dropdown-menu" role="menu" aria-labelledby="drop-1">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="/home/logout">Logout</a></li>
        </ul>

      </li>
    </ul>
  </div>
</div>