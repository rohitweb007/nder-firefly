<?php

class Target extends Eloquent {

  protected $guarded = array('id', 'created_at', 'updated_at');
  public static $rules   = array(
      'fireflyuser_id' => 'required|exists:users,id|numeric',
      'account_id'     => 'required|integer|exists:accounts,id',
      'amount'         => 'required|numeric|between:-65536,65536',
      'description'    => 'required|between:1,500',
      'duedate'        => 'before:2038-01-01|after:1980-01-01',
      'startdate'      => 'required|before:2038-01-01|after:1980-01-01',
      'closed'         => 'required|between:0,1|numeric'
  );

  public function transfers() {
    return $this->hasMany('Transfer');
  }

  public static function getHomeOverview() {
    $db   = Auth::user()->targets()->where('closed', '=', 0)->orderBy('duedate', 'DESC')->get();
    $ids  = array();
    $data = array();
    foreach ($db as $t) {
      $ids[]                = intval($t->id);
      $tr                   = array(
          'id'          => $t->id,
          'description' => Crypt::decrypt($t->description),
          'amount'      => floatval($t->amount),
          'duedate'     => $t->duedate != '0000-00-00' ? new DateTime($t->duedate) : null,
          'startdate'   => $t->startdate != '0000-00-00' ? new DateTime($t->startdate) : null,
          'account'     => intval($t->account_id),
          'saved'       => 0
      );
      $tr['pct']            = round(($tr['saved'] / $tr['amount']) * 100, 2);
      $data[intval($t->id)] = $tr;
    }
    if (count($ids) > 0) {
      $transfers = Auth::user()->transfers()->whereIn('target_id', $ids)->where('date', '<=', Session::get('period')->format('Y-m-d'))->get();
      foreach ($transfers as $t) {

        if ($t->account_from == $data[$t->target_id]['account']) {
          $data[intval($t->target_id)]['saved'] -= floatval($t->amount);
        } else if ($t->account_to == $data[$t->target_id]['account']) {
          $data[intval($t->target_id)]['saved'] += floatval($t->amount);
        }
      }
    }
    return $data;
  }

  public function hassaved(DateTime $date = null) {
    $date      = is_null($date) ? clone Session::get('period') : $date;
    // check it!
    $transfers = $this->transfers()->where('date', '<=', $date->format('Y-m-d'))->get();

    $sum = 0;
    foreach ($transfers as $t) {
      if ($t->account_from == $this->account_id) {
        $sum -= floatval($t->amount);
      } else if (($t->account_to == $this->account_id)) {
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
  public function guide(DateTime $date = null, $ignoresaved = false) {
    $date = is_null($date) ? clone Session::get('period') : $date;
    $end  = $this->duedate != '0000-00-00' ? new DateTime($this->duedate) : new DateTime('now');
    if ($end < $date) {
      return 0;
    }
    $diff = $date->diff($end);
    if ($diff->days > 0) {
      if ($ignoresaved === false) {
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

    if ($this->duedate == '0000-00-00') {
      return null;
    }
    $start = new DateTime($this->startdate);
// guide voor de hele periode:
    $due   = new DateTime($this->duedate);
    if ($date > $due) {
      return 0;
    }
    $guide = $this->guide($start);
// days since start:
    $diff  = $start->diff($date); // hoeveel dagen al onderweg?
    return $diff->days * $guide;
  }

}