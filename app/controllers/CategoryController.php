<?php

use Carbon\Carbon as Carbon;

class CategoryController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    $key = cacheKey('Categories', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data       = array();
      $categories = Auth::user()->categories()->orderBy('id', 'ASC')->get();
      // to get the avg per month we first need the number of months

      foreach ($categories as $cat) {
        $cate = array(
            'id'   => intval($cat->id),
            'name' => Crypt::decrypt($cat->name),
        );

        $now           = new Carbon('now');
        $thisMonth     = $cat->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $now->format('m-Y'))->sum('amount');
        $cate['month'] = floatval($thisMonth);

        $data[] = $cate;
      }
      Cache::put($key, $data, 1440);
    }
    return View::make('categories.all')->with('categories', $data);
  }

  public function editCategory($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      return View::make('categories.edit')->with('category', $category);
    } else {
      return App::abort(404);
    }
  }

  public function doEditCategory($id) {
    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $category->name = Input::get('name');
      $validator      = Validator::make($category->toArray(), Category::$rules);
      if ($validator->fails()) {
        Log::error('Could not edit category for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($category, true));
        return Redirect::to('/home/category/edit/' . $category->id)->withErrors($validator)->withInput();
      } else {
        $category->name = Crypt::encrypt($category->name);
        $category->save();
        return Redirect::to('/home/categories');
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteCategory($id) {

    $category = Auth::user()->categories()->find($id);
    if ($category) {
      $category->delete();
      return Redirect::to('/home/categories');
    } else {
      return App::abort(404);
    }
  }

}