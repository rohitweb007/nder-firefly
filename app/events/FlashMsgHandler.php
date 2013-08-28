<?php

class FlashMsgHandler {

  public function onEloquentCreated($event) {
    if (!defined('LESSEVENTS')) {
      $class  = strtolower(get_class($event));
      $ignore = array('balancedatapoint','tag', 'budgetpredictionpoint', 'accountpredictionpoint', 'beneficiary', 'category');

      // flash success message:
      if (!in_array($class, $ignore)) {
        Session::flash('success', 'The new ' . $class . ' has been created.');
      }
    }
  }

  /**
   * Register the listeners for the subscriber.
   *
   * @param  Illuminate\Events\Dispatcher  $events
   * @return array
   */
  public function subscribe($events) {
    $events->listen('eloquent.created: *', 'FlashMsgHandler@onEloquentCreated');
  }

}

$subscriber = new FlashMsgHandler;
Event::subscribe($subscriber);