<?php

require_once 'google/appengine/api/users/UserService.php';
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;

App::before(function($request) {
          BaseController::_determinePeriod();
          //
        });


App::after(function($request, $response) {
          //
        });


Route::filter('auth', function() {
          if (Auth::guest())
            return Redirect::guest('login');
        });

Route::filter('gs', function() {
          $user = UserService::getCurrentUser();
          if ($user) {
            $email  = $user->getEmail();
            $dbUser = Fireflyuser::where('email', $email)->remember(1440)->first();
            if (!$dbUser) {
              $dbUser           = new Fireflyuser;
              $dbUser->email    = $email;
              $dbUser->password = Hash::make(Str::random(32));
              $dbUser->save();
            }
            // save the default settings if not there:
            $defaultAmount = $dbUser->settings()->where('name', '=', 'defaultAmount')->remember(1440)->first();
            if (is_null($defaultAmount)) {
              $defaultAmount                 = new Setting;
              $defaultAmount->fireflyuser_id = $dbUser->id;
              $defaultAmount->name           = 'defaultAmount';
              $defaultAmount->value          = Crypt::encrypt(1000);
              $defaultAmount->save();
            }
            // budget behaviour:
            $bb = $dbUser->settings()->where('name', '=', 'budgetBehaviour')->remember(1440)->first();
            if (is_null($bb)) {
              $bb                 = new Setting;
              $bb->fireflyuser_id = $dbUser->id;
              $bb->name           = 'budgetBehaviour';
              $bb->value          = Crypt::encrypt('substract');
              $bb->save();
            }
            // prediction chart behaviour:
            $pcb = $dbUser->settings()->where('name', '=', 'correctPredictionChart')->remember(1440)->first();
            if(is_null($pcb)) {
              $pcb                 = new Setting;
              $pcb->fireflyuser_id = $dbUser->id;
              $pcb->name           = 'correctPredictionChart';
              $pcb->value          = Crypt::encrypt('false');
              $pcb->save();
            }
            Auth::loginUsingId($dbUser->id);
          }
          // since the user is now present and accounted for, we can define
          // some cache sections that will be used throughout the application
          // to make sure not EVERYTHING is dropped right the second something changes.
          define('TRANSACTIONS', $dbUser->id . '-transactions');
          define('CHARTS',$dbUser->id.'-charts');
        });



Route::filter('auth.basic', function() {
          return Auth::basic();
        });

/*
  |--------------------------------------------------------------------------
  | Guest Filter
  |--------------------------------------------------------------------------
  |
  | The "guest" filter is the counterpart of the authentication filters as
  | it simply checks that the current user is not logged in. A redirect
  | response will be issued if they are, which you may freely change.
  |
 */

Route::filter('guest', function() {
          if (Auth::check())
            return Redirect::to('/');
        });

/*
  |--------------------------------------------------------------------------
  | CSRF Protection Filter
  |--------------------------------------------------------------------------
  |
  | The CSRF filter is responsible for protecting your application against
  | cross-site request forgery attacks. If this special token in a user
  | session does not match the one given in this request, we'll bail.
  |
 */

Route::filter('csrf', function() {
          if (Session::token() != Input::get('_token')) {
            throw new Illuminate\Session\TokenMismatchException;
          }
        });