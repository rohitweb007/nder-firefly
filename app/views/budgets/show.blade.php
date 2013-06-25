@extends('layouts.main')
@section('content')
<script>
  var ID = parseInt("{{$budget->id}}");
</script>
<div class="row-fluid">
  <div class="span12">
    <h3>Budget {{Crypt::decrypt($budget->name)}}</h3>
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
        <td>{{mf($budget->amount)}}</td>
      </tr>
      <tr>
        <td>Spent</td>
        <td>{{mf($budget->spent())}}</td>
      </tr>
      <tr>
        <td>Left</td>
        <td>{{mf($budget->left())}}</td>
      </tr>
      <tr>
        <td>Avg spent per day</td>
        <td>{{mf($budget->avgspent)}}</td>
      </tr>
      <tr>
        <td>Avg spending target</td>
        <td>{{mf($budget->spenttarget)}}</td>
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
      @foreach($categories as $category)
      <tr>
        <td>{{HTML::Link('/home/category/overview/'.$category['id'],$category['name'])}}</td>
        <td>{{mf($category['spent'])}}</td>
        <td>{{round(($category['spent'] / $budget->spent())*100,1)}}%</td>
      </tr>
      @endforeach
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
      @foreach($beneficiaries as $beneficiary)
      <tr>
        <td>{{HTML::Link('/home/beneficiary/overview/'.$beneficiary['id'],$beneficiary['name'])}}</td>
        <td>{{mf($beneficiary['spent'])}}</td>
        <td>{{round(($beneficiary['spent'] / $budget->spent())*100,1)}}%</td>
      </tr>
      @endforeach
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
      @foreach($budget->transactions()->orderBy('date','DESC')->get() as $t)
      <tr>
        <td>{{date('d F',strtotime($t->date))}}</td>
        <td>{{Crypt::decrypt($t->description)}}</td>
        <td>
          @if(!is_null($t->account_id))
            {{Crypt::decrypt($t->account()->first()->name)}}
          @endif
        </td>
        <td>
          @if(!is_null($t->category_id))
            {{Crypt::decrypt($t->category()->first()->name)}}
          @endif
        </td>
        <td>
          @if(!is_null($t->beneficiary_id))
            {{Crypt::decrypt($t->beneficiary()->first()->name)}}
          @endif
        </td>
        <td>{{mf($t->amount)}}</td>
        <td>
          <?php echo mf($total); ?>
          <?php $total += $t->amount; ?>
        </td>
      </tr>

      @endforeach
  </div>
</div>

<script src="/js/budget.js"></script>

@endsection