<?php
class Icon extends Eloquent {
  public function categories() {
    return $this->hasMany('Category');
  }
}