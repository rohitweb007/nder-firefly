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

  public function showOverview($id) {
    $beneficiary = Auth::user()->beneficiaries()->find($id);
    if ($beneficiary) {
      return View::make('beneficiaries.overview')->with('beneficiary', $beneficiary);
    } else {
      return App::abort(404);
    }
  }

  /**
   * Same but a longer date range
   * TODO combine and smarter call.
   * @param type $id
   * @return type
   */
  public function overviewGraph($id = 0) {
    $key = cacheKey('Beneficiary', 'overviewGraph', $id, Session::get('period'));
    if (Cache::has($key)) {
      //return Response::json(Cache::get($key));
    }
    $today       = clone Session::get('period');
    $end         = clone($today);
    $past        = self::getFirst();
    $beneficiary = Auth::user()->beneficiaries()->find($id);

    $data = array(
        'cols' => array(
            array(
                'id'    => 'date',
                'label' => 'Date',
                'type'  => 'date',
                'p'     => array('role' => 'domain')
            ),
            array(
                'id'    => 'spent',
                'label' => 'Spent',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
            array(
                'type' => 'boolean',
                'p'    => array(
                    'role' => 'certainty'
                )
            ),
            array(
                'id'    => 'earned',
                'label' => 'Earned',
                'type'  => 'number',
                'p'     => array('role' => 'data')
            ),
            array(
                'type' => 'boolean',
                'p'    => array(
                    'role' => 'certainty'
                )
            ),
        ),
        'rows' => array()
    );

    $index = 0;
    //$balance = $account->balance($past);
    while ($past <= $end) {
      $month                             = intval($past->format('n')) - 1;
      $year                              = intval($past->format('Y'));
      $day                               = intval($past->format('j'));
      $data['rows'][$index]['c'][0]['v'] = 'Date(' . $year . ', ' . $month . ', ' . $day . ')';

      $spent                             = floatval($beneficiary->transactions()->where('amount', '<', 0)->where('date', '=', $past->format('Y-m-d'))->sum('amount')) * -1;
      $earned                            = floatval($beneficiary->transactions()->where('amount', '>', 0)->where('date', '=', $past->format('Y-m-d'))->sum('amount'));
      $certain_spent                     = true;
      $certain_earned                    = true;
      $data['rows'][$index]['c'][1]['v'] = $spent;
      $data['rows'][$index]['c'][2]['v'] = $certain_spent;
      $data['rows'][$index]['c'][3]['v'] = $earned;
      $data['rows'][$index]['c'][4]['v'] = $certain_earned;
      $past->add(new DateInterval('P1D'));
      $index++;
    }

    Cache::put($key, $data, 1440);
    return Response::json($data);
  }

}