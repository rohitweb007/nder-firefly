<?php

class Tag extends Eloquent {


  public function transactions() {
    return $this->belongsToMany('Transaction');
  }
}