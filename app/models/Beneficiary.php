<?php
class Beneficiary extends Eloquent {
  public static $rules = array(
      'fireflyuser_id' => 'required|exists:users,id',
      'name'    => 'required|between:1,50'
  );

  public function transactions() {
    return $this->hasMany('Transaction');
  }

}