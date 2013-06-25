<?php

class Target extends Eloquent {

  public static $rules = array(
      'fireflyuser_id' => 'required|exists:users,id|numeric',
      'account_id'     => 'required|integer|exists:accounts,id',
      'amount'         => 'required|numeric|between:-65536,65536',
      'description'    => 'required|between:1,500',
      'duedate'        => 'before:2038-01-01|after:1980-01-01',
      'startdate'      => 'required|before:2038-01-01|after:1980-01-01',
  );

}