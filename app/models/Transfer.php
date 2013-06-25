<?php

class Transfer extends Eloquent {

  public static $rules = array(
      'fireflyuser_id'   => 'required|exists:users,id',
      'description'      => 'required|between:1,500',
      'ignoreprediction' => 'required|between:0,1',
      'countasexpense'   => 'required|between:0,1',
      'date'             => 'required|before:2038-01-01|after:1980-01-01',
      'amount'           => 'required|numeric|between:0.01,65536',
      'account_from'     => 'required|integer|exists:accounts,id|different:account_to',
      'account_to'       => 'required|integer|exists:accounts,id',
      'category_id'      => 'integer|exists:categories,id',
      'budget_id'        => 'integer|exists:budgets,id',
      'target_id'        => 'integer|exists:targets,id',
  );

  public function accountFrom() {
    return $this->belongsTo('Account','account_from');
  }

  public function accountTo() {
    return $this->belongsTo('Account','account_to');
  }
  public function budget() {
    return $this->belongsTo('Budget');
  }

}