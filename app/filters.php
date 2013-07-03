<?php
require_once 'google/appengine/api/users/UserService.php';
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;


/*
  |--------------------------------------------------------------------------
  | Application & Route Filters
  |--------------------------------------------------------------------------
  |
  | Below you will find the "before" and "after" events for the application
  | which may be used to do any work before or after a request into your
  | application. Here you may also register your custom route filters.
  |
 */

App::before(function($request) {
  BaseController::_determinePeriod();
          //
        });


App::after(function($request, $response) {
          //
        });

/*
  |--------------------------------------------------------------------------
  | Authentication Filters
  |--------------------------------------------------------------------------
  |
  | The following filters are used to verify that the user of the current
  | session is logged into this application. The "basic" filter easily
  | integrates HTTP Basic authentication for quick, simple checking.
  |
 */

Route::filter('auth', function() {
          if (Auth::guest())
            return Redirect::guest('login');
        });

Route::filter('gs', function() {
    $user = UserService::getCurrentUser();
    if ($user) {
        $email = $user->getEmail();
        $dbUser = Fireflyuser::where('email',$email)->first();
        if(!$dbUser) {
          $dbUser = new Fireflyuser;
          $dbUser->email = $email;
          $dbUser->password = Hash::make(Str::random(32));
          $dbUser->save();
        }
        // save the default settings if not there:
        $defaultAmount = $dbUser->settings()->where('name','=','defaultAmount')->first();
        if(is_null($defaultAmount)) {
          $defaultAmount = new Setting;
          $defaultAmount->fireflyuser_id = $dbUser->id;
          $defaultAmount->name = 'defaultAmount';
          $defaultAmount->value = Crypt::encrypt(1000);
          $defaultAmount->save();
        }

        Auth::loginUsingId($dbUser->id);

    }
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