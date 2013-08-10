<?php

class Setting extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'fireflyuser_id' => 'required|exists:users,id',
      'name'           => 'required|between:1,300',
      'value'          => 'required|min:0',
      'date'           => 'before:2038-01-01|after:1970-01-01'
  );

  public function user() {
    return $this->belongsTo('User');
  }

  public static function getSetting($name, $date = null) {
    $setting = Auth::user()->settings()->where('date', '=', $date)->where('name', '=', $name)->first();
    if (!is_null($setting)) {
      return Crypt::decrypt($setting->value);
    }
    return null;
  }

}

?>
