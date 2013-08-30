<?php
 
use Carbon\Carbon as Carbon;

class BeneficiaryController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showAll() {
    $key = cacheKey('Beneficiaries', 'showAll');
    if (Cache::has($key)) {
      $data = Cache::get($key);
    } else {
      $data          = array();
      $beneficiaries = Auth::user()->beneficiaries()->orderBy('id', 'ASC')->get();
      // to get the avg per month we first need the number of months
      foreach ($beneficiaries as $ben) {
        $name = Crypt::decrypt($ben->name);
        $bene = array(
            'id'   => intval($ben->id),
            'name' => $name,
        );

        $now           = new Carbon('now');
        $thisMonth     = $ben->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $now->format('m-Y'))->sum('amount');
        $bene['month'] = floatval($thisMonth);

        $data[] = $bene;
      }
      unset($name);$name=array();
      // order by alfabet
      // Obtain a list of columns
      foreach ($data as $key => $row) {
        $id[$key]  = $row['id'];
        $name[$key] = $row['name'];
      }
      array_multisort($name, SORT_ASC, $id, SORT_DESC, $data);

      Cache::put($key, $data, 1440);
    }
    return View::make('beneficiaries.all')->with('beneficiaries', $data);
  }

  public function editBeneficiary($id) {
    $beneficiary = Auth::user()->beneficiaries()->find($id);
    if ($beneficiary) {
      return View::make('beneficiaries.edit')->with('beneficiary', $beneficiary);
    } else {
      return App::abort(404);
    }
  }

  public function doEditBeneficiary($id) {
    $beneficiary = Auth::user()->beneficiaries()->find($id);
    if ($beneficiary) {
      $beneficiary->name = Input::get('name');
      $validator         = Validator::make($beneficiary->toArray(), Beneficiary::$rules);
      if ($validator->fails()) {
        Log::error('Could not edit beneficiary for user ' . Auth::user()->email . ': ' . print_r($validator->messages()->all(), true) . ' Budget: ' . print_r($beneficiary, true));
        return Redirect::to('/home/beneficiary/edit/' . $beneficiary->id)->withErrors($validator)->withInput();
      } else {
        $beneficiary->name = Crypt::encrypt($beneficiary->name);
        $beneficiary->save();
        return Redirect::to('/home/beneficiaries');
      }
    } else {
      return App::abort(404);
    }
  }

  public function deleteBeneficiary($id) {

    $beneficiary = Auth::user()->beneficiaries()->find($id);
    if ($beneficiary) {
      $beneficiary->delete();
      return Redirect::to('/home/beneficiaries');
    } else {
      return App::abort(404);
    }
  }

  public function showOverview($id) {
    $beneficiary = Auth::user()->beneficiaries()->find($id);
    if ($beneficiary) {
      return View::make('beneficiaries.overview')->with('beneficiary', $beneficiary);
    } else {
      return App::abort(404);
    }
  }
}