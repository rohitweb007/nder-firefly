<?php

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
      $first         = BaseController::getFirst();
      $last          = BaseController::getLast();
      $diff          = $first->diff($last);
      $months        = $diff->m + ($diff->y * 12);

      foreach ($beneficiaries as $ben) {
        $bene        = array(
            'id'   => intval($ben->id),
            'name' => Crypt::decrypt($ben->name),
        );
        $trans       = $ben->transactions()->sum('amount');
        $bene['avg'] = $trans / $months;

        $now           = new DateTime('now');
        $thisMonth     = $ben->transactions()->where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $now->format('m-Y'))->sum('amount');
        $bene['month'] = floatval($thisMonth);

        $data[] = $bene;
      }
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
        Cache::flush();
        Session::flash('success', 'The beneficiary has been edited.');
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
      Cache::flush();
      Session::flash('success', 'The beneficiary has been deleted.');
      return Redirect::to('/home/beneficiaries');
    } else {
      return App::abort(404);
    }
  }

}