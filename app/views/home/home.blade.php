@extends('layouts.main')
@section('content')
<div class="row-fluid">
  <div class="span6">
    <h4>Accounts</h4>
    @if(count($accounts) == 0)
      <p><em>You have no accounts defined yet.</em></p>
      <p><em>Your first step should be to </em> <strong>{{HTML::Link('/home/account/add','add a new account')}}</strong></p>
    @else
    <?php $sum = 0;?>
    <table class="table table-condensed table-striped">
      @foreach($accounts as $account)
      <?php $sum += $account->balance(); ?>
        <tr>
          <th style="width:30%;">
            @if($account->currentbalance > 0)
            {{HTML::Link('/home/account/overview/'.$account->id,$account->name)}}
            @else
            {{HTML::Link('/home/account/overview/'.$account->id,$account->name,array('style' => 'color:red;','class' => 'tt','title' => $account->name.' has a balance below zero. Try to fix this.'))}}
            @endif
            @if($account->maxpct < 30 && $account->minpct < 30)
              &nbsp;&nbsp;&nbsp;<small style="font-weight:normal;">{{mf($account->currentbalance)}}</small>
            @endif
          </th>
          <td style="width:35%;border-bottom:1px #ddd solid;">
            @if($account->minpct > 0)
              <div style="margin:0;" class="progress progress-striped"><div class="bar bar-danger" style="width:{{$account->minpct}}%;display: block; float: right;text-align:right;">{{mf($account->currentbalance)}}&nbsp;</div></div>
            @endif
          </td>
          <td style="width:30%;border-bottom:1px #ddd solid;">
            @if($account->maxpct > 0)
              <div style="margin:0;" class="progress progress-striped"><div class="bar bar-success" style="width:{{$account->maxpct}}%;text-align:left;">
                  @if($account->maxpct > 30)
                  &nbsp;{{mf($account->currentbalance)}}
                  @endif
                </div></div>
            @endif
          </td>
          <td style="width:5%;">
            @if(count($account->list) > 0)
            <i data-value="Account{{$account->id}}" class="showTransactions icon-folder-close"></i>
            @endif
          </td>
        </tr>
        <tr>
        </tr>
        <tr style="display:none;"><td colspan="2"></td></tr>
        <tr>
          <td colspan="4">
            <div class="accountOverviewGraph" data-value="{{$account->id}}" id="accountOverviewGraph{{$account->id}}"></div>
          </td>
        </tr>
        @if(count($account->list) > 0)
        <tr style="display:none;"><td colspan="2"></td></tr>
        <tr>
          <td style="border-top:0;" colspan="4">
            <table class="table table-condensed table-bordered" class="fade in" style="display:none;" id="Account{{$account->id}}Table">
              @foreach($account->list as $list)
              @foreach($list as $t)
                <tr>
                  <td>{{date('d F',strtotime($t->date))}}</td>
                  @if($t instanceof Transaction)
                    <td>{{HTML::Link('/home/transaction/edit/'.$t->id,Crypt::decrypt($t->description))}}</td>
                    <td>
                      @if(!is_null($t->category_id))
                        {{HTML::Link('/home/category/overview/'.$t->category()->first()->id,Crypt::decrypt($t->category()->first()->name))}}
                      @endif
                    </td>
                  @else
                    <td>{{HTML::Link('/home/transfer/edit/'.$t->id,Crypt::decrypt($t->description))}}</td>
                    @if($t instanceof Transfer && $t->account_from == $account->id)
                      <td>&rarr; {{HTML::Link('/home/account/overview/' . $t->account_to,Crypt::decrypt($t->accountto()->first()->name))}}</td>
                    @elseif($t instanceof Transfer && $t->account_to == $account->id)
                      <td>&larr; {{HTML::Link('/home/account/overview/' . $t->account_from,Crypt::decrypt($t->accountfrom()->first()->name))}}</td>
                    @endif
                  @endif
                  <td>
                    @if($t instanceof Transfer && $t->account_from == $account->id)
                      {{mf($t->amount * -1)}}
                    @else
                      {{mf($t->amount)}}
                    @endif
                  </td>
                </tr>
              @endforeach
              @endforeach

            </table>
          </td>
        </tr>
        @endif

      @endforeach


      <tr>
        <td colspan="2" style="text-align:right;"><em>Total:</em></td>
        <td>{{mf($sum)}}</td>
      </tr>
    </table>
    @endif
    </div>

  @if(count($accounts) > 0)
  <div class="span6">
    <h4>Budgets</h4>
      @if(count($budgets) < 1)
        <p>
          <em>Your next step should be to add a budget. A budget can help you accurately track expenses.</em><br />
          {{HTML::link('/home/budget/add','Create a budget now.')}}
        </p>
      @else
      <table class="table table-condensed table-striped">
        @foreach($budgets as $budget)
        <tr>
          <th style="width:30%;">
            @if($budget->overflow === false)
              <a href="/home/budget/overview/{{$budget->id}}">{{$budget->name}}</a>
            @else
            <a href="/home/budget/overview/{{$budget->id}}" class="tt" title="Firefly predicts you will overspend on this budget!">{{$budget->name}}</a>
            @endif
          </th>
          <td style="width:65%;">
            @if($budget->amount > 0 && $budget->amount >= $budget->spent())
              <div style="margin:0;" class="progress progress-striped"><div class="bar
                                                                            @if($budget->overflow)
                                                                              bar-warning
                                                                            @else
                                                                              bar-success
                                                                            @endif
                                                                            " style="width:{{$budget->widthpct}}%;text-align:left;">
                  @if($budget->widthpct > 5)
                  &nbsp;{{mf($budget->spent())}}
                  @endif
                  </div></div>
            @else
            @endif
          </td>
          <td style="width:5%">
            <i data-value="Budget{{$budget->id}}" class="showTransactions icon-folder-close"></i>
          </td>
          <!--<td><small><span class="tt" title="The total amount of money in the budget.">{{mf($budget->amount)}}</span> / <span class="tt" title="The amount of money left">{{mf($budget->left())}}</span> / <span class="tt" title="The adviced spending per day.">{{mf($budget->advice())}}</span> / <span class="tt" title="Expected expenses for this budget">{{mf($budget->expected())}}</span></small></td>-->
        </tr>
        <tr>

        </tr>
        <tr style="display:none;"><td colspan="3"></td></tr>
        <tr>
          <td colspan="3">
            <div class="budgetOverviewGraph" data-value="{{$budget->id}}" id="budgetOverviewGraph{{$budget->id}}"></div>
          </td>
        </tr>
        @if(count($budget->list) > 0)
        <tr style="display:none;"><td colspan="2"></td></tr>
        <tr>
          <td style="border-top:0;" colspan="4">
            <table class="table table-condensed table-bordered" class="fade in" style="display:none;" id="Budget{{$budget->id}}Table">
              @foreach($budget->list as $list)
              @foreach($list as $t)
                <tr>
                  <td>{{date('d F',strtotime($t->date))}}</td>
                  @if($t instanceof Transaction)
                    <td>{{HTML::Link('/home/transaction/edit/'.$t->id,Crypt::decrypt($t->description))}}</td>
                    <td>
                      @if(!is_null($t->category_id))
                        {{HTML::Link('/home/category/overview/'.$t->category()->first()->id,Crypt::decrypt($t->category()->first()->name))}}
                      @endif
                    </td>
                  @else
                    <td>{{HTML::Link('/home/transfer/edit/'.$t->id,Crypt::decrypt($t->description))}}</td>
                    @if($t instanceof Transfer && $t->account_from == $account->id)
                      <td>&rarr; {{HTML::Link('/home/account/overview/' . $t->account_to,Crypt::decrypt($t->accountto()->first()->name))}}</td>
                    @elseif($t instanceof Transfer && $t->account_to == $account->id)
                      <td>&larr; {{HTML::Link('/home/account/overview/' . $t->account_from,Crypt::decrypt($t->accountfrom()->first()->name))}}</td>
                    @endif
                  @endif
                  <td>
                    @if($t instanceof Transfer && $t->account_from == $account->id)
                      {{mf($t->amount * -1)}}
                    @else
                      {{mf($t->amount)}}
                    @endif
                  </td>
                </tr>
              @endforeach
              @endforeach

            </table>
          </td>
        </tr>
        @endif
        @endforeach
        </table>
      @endif
  </div>
  @endif
</div>
<div class="row-fluid">
  <div class="span12">
    <h4>Overspending</h4>
    <div id="ovcat"></div>
  </div>
</div>
<script src="/js/home.js"></script>
@endsection