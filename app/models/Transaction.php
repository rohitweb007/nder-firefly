<?php

class Transaction extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'fireflyuser_id' => 'required|exists:users,id|numeric',
      'account_id'     => 'required|integer|exists:accounts,id',
      'beneficiary_id' => 'integer|exists:beneficiaries,id',
      'category_id'    => 'integer|exists:categories,id',
      'budget_id'      => 'integer|exists:budgets,id',
      'date'           => 'required|before:2038-01-01|after:1980-01-01',
      'description'    => 'required|between:1,255',
      'amount'         => 'required|numeric|between:-65536,65536|not_in:0',
      'onetime'        => 'required|numeric|between:0,1'
  );

  public function tags() {
    return $this->belongsToMany('Tag');
  }

  public function account() {
    return $this->belongsTo('Account');
  }

  public function category() {
    return $this->belongsTo('Category');
  }

  public function beneficiary() {
    return $this->belongsTo('Beneficiary');
  }

  public function budget() {
    return $this->belongsTo('Budget');
  }

  public function tagList() {
    $tags = $this->tags()->get();
    $arr = array();
    foreach($tags as $tag) {
      $arr[] = $tag->tag;
    }
    return join(',',$arr);
  }

}
