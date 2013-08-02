<?php

class Budgetpredictionpoint extends Eloquent {

  public function budget() {
    return $this->belongsTo('Budget');
  }


}