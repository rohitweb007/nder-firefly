<?php

class Tag extends Eloquent {


  public static $rules = array(
      'fireflyuser_id' => 'exists:users,id',
      'tag' => 'required|between:1,90'
  );


  public function transactions() {
    return $this->belongsToMany('Transaction');
  }

  public static function findOrCreate($tag) {
    $existing = Auth::user()->tags()->where('tag','=',$tag)->first();
    if(is_null($existing)) {

      $newTag = new Tag;
      $newTag->tag = $tag;
      $tag = Auth::user()->tags()->save($newTag);
      return $tag;
    }
    return $existing;
  }
}