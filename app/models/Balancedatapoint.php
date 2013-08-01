<?php
class Balancedatapoint extends Eloquent {
  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'account_id'     => 'required|integer|exists:accounts,id',
      'date'           => 'required|before:2038-01-01|after:1980-01-01',
      'amount'         => 'required|numeric|between:-65536,65536',
  );

}