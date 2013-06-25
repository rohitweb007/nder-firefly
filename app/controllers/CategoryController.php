<?php

class CategoryController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    return View::make('categories.all')->with('categories',Auth::user()->categories()->get());
  }

  public function showSingle($id) {
    return View::make('categories.single')->with('category',Auth::user()->categories()->find($id));
  }

}