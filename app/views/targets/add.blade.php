@extends('layouts.main')
@section('content')
<div class="row-fluid">
  <div class="span6">
    <h3>Add a new target</h3>
    <p>
      Please fill in the following details about your new saving target.
    </p>
    <p>
      Targets work by moving money (using transfers) to a certain account; Firefly
      assumes you'll have two accounts: one normal, one savings-account. So
      you'll need to define at least the account on which the saved money resides.
    </p>
    <p>
      Saving targets only work when you have two or more accounts.
    </p>

    {{Form::open(array('class' => 'form-horizontal'))}}
    <div class="control-group">
      <label class="control-label" for="inputDescription">Target description</label>
      <div class="controls">
        {{Form::text('description',null,array('id' => 'inputDescription','autocomplete' => 'off','class' => 'input-xxlarge','placeholder' => 'A new bike'))}}
        <br /><span class="text-error"><?php echo $errors->first('description'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputAmount">Target saving amount</label>
      <div class="controls">
        {{Form::input('number', 'amount',null,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputAmount','placeholder' => '&euro;'))}}
        &nbsp;&nbsp;<img  class="tt" title="How much do you want to save? Enter 0 for no limit." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('amount'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputStartdate">Start date</label>
      <div class="controls">
        {{Form::input('date', 'startdate',date('Y-m-d'),array('id' => 'inputStartdate','autocomplete' => 'off','placeholder' => date('m/d/Y')))}}
        &nbsp;&nbsp;<img class="tt" title="When did you start saving? Defaults to today" src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('startdate'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDuedate">Due date</label>
      <div class="controls">
        {{Form::input('date', 'duedate',date('Y-m-d',time() + (31*24*3600)),array('id' => 'inputDuedate','autocomplete' => 'off','placeholder' => date('m/d/Y')))}}
        &nbsp;&nbsp;<img  class="tt" title="When do you want to have the money collected? Optional field." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('duedate'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputAccount">Target saving account</label>
      <div class="controls">
        {{Form::select('account',$accounts)}}
        <br /><span class="text-error"><?php echo $errors->first('account_id'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save new target" />
      </div>
    </div>

    {{Form::close()}}

  </div>
</div>


@endsection