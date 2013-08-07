<?php

class TagsController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function testTags() {
    $transaction = Auth::user()->transactions()->first();

    $boom = new Tag(array('fireflyuser_id' => Auth::user()->id,'tag' => 'Boom'));

    $roos = '';

    $vis = '';


    var_dump($transaction->tags()->get());
  }

}