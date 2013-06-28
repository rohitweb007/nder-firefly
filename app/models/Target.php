<?php

class Target extends Eloquent {

  public static $rules = array(
      'fireflyuser_id' => 'required|exists:users,id|numeric',
      'account_id'     => 'required|integer|exists:accounts,id',
      'amount'         => 'required|numeric|between:-65536,65536',
      'description'    => 'required|between:1,500',
      'duedate'        => 'before:2038-01-01|after:1980-01-01',
      'startdate'      => 'required|before:2038-01-01|after:1980-01-01',
  );

  public function transfers() {
    return $this->hasMany('Transfer');
  }

  public function hassaved(DateTime $date = null) {
    $date = is_null($date) ? clone Session::get('period') : $date;
    // check it!
    $transfers = $this->transfers()->where('date','<=',$date->format('Y-m-d'))->get();
    $sum = 0;
    foreach($transfers as $t) {
      if($t->account_from == $this->account_id) {
        $sum -= floatval($t->amount);
      } else {
        $sum += floatval($t->amount);
      }
    }
    return floatval($sum);
  }

  /**
   * Daily saving guideline.
   * @param DateTime $date
   * @return int
   */
  public function guide(DateTime $date = null,$ignoresaved = false) {
    $date = is_null($date) ? clone Session::get('period') : $date;
    $end = $this->duedate != '0000-00-00' ? new DateTime($this->duedate) : new DateTime('now');

    $diff = $date->diff($end);
    if($diff->days > 0) {
      if($ignoresaved === false) {
        $amount = $this->amount - $this->hassaved($date);
      } else {
        $amount = $this->amount;
      }


      $guide = $amount / $diff->days;
      return $guide;

    } else {
      return 0;
    }
  }

  public function shouldhavesaved(DateTime $date = null) {
    $date = is_null($date) ? clone Session::get('period') : $date;
    if($this->duedate == '0000-00-00') {
      return null;
    } else {
      $start = new DateTime($this->startdate);
      // guide voor de hele periode:
      $due = new DateTime($this->duedate);
      $guide = $this->guide($start);
      // days since start:
      $diff = $start->diff($date); // hoeveel dagen al onderweg?
      return $diff->days * $guide;
    }
  }
}