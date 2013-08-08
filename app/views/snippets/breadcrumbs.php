<?php
$segments = Request::segments();
?>

<ul class="breadcrumb">
  <li><a href="/home">Home</a> <span class="divider">/</span></li>
    <?php
    if (isset($segments[1])) {
      switch ($segments[1]) {
        default:
          break;
        case 'settings':
        case 'amounts':
          echo '<li class="active">Overview of all ' . $segments[1] . '</li>';
          break;
        case 'charts':
          if (isset($segments[2])) {
            switch ($segments[2]) {
              case 'prediction':
                echo '<li class="active">Prediction chart</li>';
                break;
              case 'compare':
                echo '<li class="active">Comparision charts</li>';
                break;
            }
          }

          break;
        case 'accounts':
        case 'beneficiaries':
        case 'budgets':
        case 'categories':
        case 'beneficiaries':
        case 'targets':
        case 'transactions':
        case 'transfers':
          echo '<li><a href="/home/' . $segments[1] . '">' . ucfirst($segments[1]) . '</a> <span class="divider">/</span></li>';
          echo '<li class="active">Overview of all ' . $segments[1] . '</li>';
          break;
        case 'account':
        case 'budget':
        case 'category':

        case 'transaction':
        case 'transfer':
        case 'beneficiary':
        case 'target':
          // account has its own overview, so it needs to be visible in the
          // edit screen
          if (isset($segments[2])) {
            echo '<li><a href="/home/' . Str::plural($segments[1]) . '">' . ucfirst(Str::plural($segments[1])) . '</a> <span class="divider">/</span></li>';
            switch ($segments[2]) {
              case 'add':
                echo '<li class="active">Add a new ' . $segments[1] . '</li>';
                break;
              case 'edit':
                if (!in_array($segments[1], array('transaction', 'transfer'))) {
                  echo '<li><a href="/home/' . $segments[1] . '/overview/' . $segments[3] . '">' . ucfirst($segments[1]) . ' overview</a> <span class="divider">/</span></li>';
                }
                echo '<li class="active">Edit ' . $segments[1] . '</li>';
                break;
              case 'overview':
                echo '<li class="active">' . ucfirst($segments[1]) . ' overview</li>';
                break;
              case 'overspending':
                echo '<li class="active">' . ucfirst($segments[1]) . ' overspending</li>';
                break;
            }
          }

          break;
      }
    }
    ?>
</ul>