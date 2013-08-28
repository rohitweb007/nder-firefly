<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Fireflyuser extends Eloquent implements UserInterface, RemindableInterface {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'users';

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = array('password');

  /**
   * Get the unique identifier for the user.
   *
   * @return mixed
   */
  public function getAuthIdentifier() {
    return $this->getKey();
  }

  /**
   * Get the password for the user.
   *
   * @return string
   */
  public function getAuthPassword() {
    return $this->password;
  }

  /**
   * Get the e-mail address where password reminders are sent.
   *
   * @return string
   */
  public function getReminderEmail() {
    return $this->email;
  }

  public function accounts() {
    return $this->hasMany('Account');
  }

  public function tags() {
    return $this->hasMany('Tag');
  }

  public function settings() {
    return $this->hasMany('Setting');
  }

  public function budgets() {
    return $this->hasMany('Budget');
  }

  public function categories() {
    return $this->hasMany('Category');
  }

  public function beneficiaries() {
    return $this->hasMany('Beneficiary');
  }

  public function targets() {
    return $this->hasMany('Target');
  }

  public function transactions() {
    return $this->hasMany('Transaction');
  }

  public function transfers() {
    return $this->hasMany('Transfer');
  }

}