@extends('layouts.main')
@section('content')
<script>
  var ID = parseInt("{{$account->id}}");
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Account {{Crypt::decrypt($account->name)}} <span id="date"></span></h3>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
    <div id="accountDashboard"></div>
    <div id="chart"></div>
    <div id="control"></div>
  </div>
</div>
<div class="row-fluid" id="listDashboard">
  <div class="span4" id="budgetDashboard">
    <h4>Budgets</h4>
    <div id="budgetTable"></div>
    </div>
  <div class="span4"><h4>Categories</h4><div id="categoryTable"></div></div>
  <div class="span4" style="border:1px red solid;"><h4>Beneficiaries</h4></div>
</div>

<script src="/js/account.js"></script>
@endsection