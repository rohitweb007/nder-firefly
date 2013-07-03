<?php

/**
 * Description of SettingsController
 *
 * @author sander
 */
class SettingsController extends BaseController {

  //put your code here

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function settings() {
    return View::make('settings.index');
  }

  public function amounts() {
    $defaultSetting = Auth::user()->settings()->where('name', '=', 'defaultAmount')->first();
    $defaultAmount  = intval(Crypt::decrypt($defaultSetting->value));

    // dates:
    $today  = self::getFirst();
    $today->modify('first day of this month midnight');
    $future = clone $today;
    $future->add(new DateInterval('P1Y'));
    $dates  = array();
    while ($today <= $future) {
      $dates[$today->format('Y-m-d')] = $today->format('F Y');
      $today->add(new DateInterval('P1M'));
    }

    // monthly amounts.
    $settings = Auth::user()->settings()->where('name', '=', 'monthlyAmount')->orderBy('date')->get();


    return View::make('settings.amounts')->with('defaultAmount', $defaultAmount)->with('dates', $dates)->with('settings', $settings);
  }

  public function update() {
    // get setting info:
    //$name = Input/
    foreach (Input::all() as $name => $value) {
      // date_ is reserved.
      if (substr($name, 0, 5) != 'date_' && $name != '_token' && substr($name, 0, 8) != 'special_') {
        // check if there is a date for this setting:
        $query = Auth::user()->settings()->where('name', '=', $name);
        if (!is_null(Input::get('date_' . $name))) {
          $date = new DateTime(Input::get('date_' . $name));
          $query->where('date', '=', $date->format('Y-m-d'));
        } else {
          $date = null;
          $query->whereNull('date');
        }
        // has existing setting?
        $setting = $query->first();
        if (is_null($setting)) {
          $setting                 = new Setting;
          $setting->fireflyuser_id = Auth::user()->id;
          $setting->date           = $date;
          $setting->name           = $name;
          $setting->save();
        }
        $setting->value = Crypt::encrypt($value);
        $setting->save();
      }
    }
    Session::flash('success', 'Settings updated!');
    Cache::flush();
    if (!is_null(Input::get('special_redirect'))) {
      return Redirect::route(Input::get('special_redirect'));
    } else {
      return Redirect::to('/home/settings');
    }
  }

  public function addSetting() {
    // needed: date (or null), name and value.
    $query = Auth::user()->settings();

    if (is_null(Input::get('name')) || is_null(Input::get('value'))) {
      return App::abort(500);
    }
    $query->where('name', '=', Input::get('name'));
    if (!is_null(Input::get('date'))) {
      $query->where('date', '=', Input::get('date'));
    }
    $r = $query->count();
    if ($r > 0) {
      Session::flash('error', 'This setting already exists!');
      if (!is_null(Input::get('special_redirect'))) {
        return Redirect::route(Input::get('special_redirect'));
      } else {
        return Redirect::to('/home/settings');
      }
    }
    $setting                 = new Setting; // we hebben geen return!
    $setting->fireflyuser_id = Auth::user()->id;
    $setting->name           = Input::get('name');
    $setting->date           = Input::get('date');
    $setting->value          = Input::get('value') == '' ? NULL : Input::get('value');

    $validator = Validator::make($setting->toArray(), Setting::$rules);
    if ($validator->fails()) {
      if (!is_null(Input::get('special_redirect'))) {
        $redirect = Redirect::route(Input::get('special_redirect'));
      } else {
        $redirect = Redirect::to('/home/settings');
      }
      return $redirect->withErrors($validator)->withInput();
    } else {
      $setting->value = Crypt::encrypt($setting->value);
      $setting->save();
      Cache::flush();
      Session::flash('success', 'Your settings have been saved.');
      if (!is_null(Input::get('special_redirect'))) {
        return Redirect::route(Input::get('special_redirect'));
      } else {
        return Redirect::to('/home/settings');
      }
    }
  }

  public function deleteSetting() {
    $setting = Auth::user()->settings()->find(Input::get('id'));
    if($setting) {
      $setting->delete();
      Cache::flush();
      return Response::json(true);
    } else {
      return Response::json(false);
    }

  }

}
