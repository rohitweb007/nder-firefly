<?php
use Carbon\Carbon as Carbon;
class Category extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'fireflyuser_id' => 'required|exists:users,id',
      'name'           => 'required|between:1,50',
      'showtrend'      => 'required|between:0,1'
  );

  public function averagespending(Carbon $date = null) {
    $date         = is_null($date) ? Session::get('period') : $date;
    $transactions = floatval($this->transactions()->
                            where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '!=', $date->format('m-Y'))->
                            where('onetime', '=', 0)->
                            where(DB::Raw('DATE_FORMAT(`date`,"%d")'), '<=', intval($date->format('j')))->sum('amount')) * -1;

    $sum          = $transactions;
    $oldest       = BaseController::getFirst();
    $diff         = $oldest->diff($date);
    $months = $diff->m + (12 * $diff->y);
    return $months > 0 ? ($sum / $months) : $sum;
  }

  public function spent(Carbon $date = null) {
    $date         = is_null($date) ? Session::get('period') : $date;
    $transactions = floatval($this->transactions()->
                            where(DB::Raw('DATE_FORMAT(`date`,"%m-%Y")'), '=', $date->format('m-Y'))->
                            where('onetime', '=', 0)->
                            sum('amount')) * -1;
    return ($transactions);
  }

  public function transactions() {
    return $this->hasMany('Transaction');
  }

  public function transfers() {
    return $this->hasMany('Transfer');
  }

}