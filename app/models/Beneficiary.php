<?php

class Beneficiary extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'fireflyuser_id' => 'required|exists:users,id',
      'name'           => 'required|between:1,50'
  );

  public function transactions() {
    return $this->hasMany('Transaction');
  }

}