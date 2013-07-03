<?php

class Setting extends Eloquent {

  public static $rules = array(
      'fireflyuser_id' => 'required|exists:users,id',
      'name'           => 'required|between:1,300',
      'value'          => 'required|min:0',
      'date'           => 'before:2038-01-01|after:1970-01-01'
  );

  public function user() {
    return $this->belongsTo('User');
  }

}

?>
