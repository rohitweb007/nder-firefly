@extends('layouts.main')
@section('content')
<div class="row-fluid">
  <div class="span6">
    <h3>Add a new account</h3>
    <p>
      Please fill in the following details about your shiny new account.

      It can be a credit card, a savings account or whatever (the system doesn't really give a crap)
      but your best bet is to add the bank account you use daily for shopping, groceries and what-not.
    </p>

    {{Form::open(array('class' => 'form-horizontal'))}}
    <div class="control-group">
      <label class="control-label" for="inputName">New account name</label>
      <div class="controls">
        {{Form::text('name',null,array('id' => 'inputName','autocomplete' => 'off','placeholder' => 'Account Name'))}}
        <br /><span class="text-error"><?php echo $errors->first('name'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="inputBalance">Account's current balance</label>
      <div class="controls">
        {{Form::input('number', 'balance',null,array('step' => 'any','autocomplete' => 'off', 'id' => 'inputBalance','placeholder' => '&euro;'))}}
        &nbsp;&nbsp;<img class="tt" title="Enter the account's current balance." src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('balance'); ?></span>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="inputDate">Balance date</label>
      <div class="controls">
        {{Form::input('date', 'date',date('Y-m-d'),array('id' => 'inputDate','autocomplete' => 'off','placeholder' => date('m/d/Y')))}}
        &nbsp;&nbsp;<img class="tt" title="When did you last check this balance?" src="/img/icons/help.png" alt="Help on this field" />
        <br /><span class="text-error"><?php echo $errors->first('date'); ?></span>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="Save new account" />
      </div>
    </div>

    {{Form::close()}}

  </div>
</div>


@endsection