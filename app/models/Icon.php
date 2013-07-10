<?php

class Icon extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');

  public function categories() {
    return $this->hasMany('Category');
  }

}