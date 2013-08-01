<?php

class CacheRelatedEventHandler {

  /**
   * Register the listeners for the subscriber.
   *
   * @param  Illuminate\Events\Dispatcher  $events
   * @return array
   */
  public function onEloquentCreated($event) {
    $class  = strtolower(get_class($event));
    $ignore = array('balancedatapoint');

    // flash success message:
    if (!in_array($class, $ignore)) {
      Session::flash('success', 'The new ' . $class . ' has been created.');
    }


    // clean up balance data points if necessary:
    switch ($class) {
      case 'transaction':
        $account = Account::find($event->account_id);
        if ($account) {
          $account->balancedatapoints()->where('date', '>=', $event->date)->delete();
        }
        break;
      case 'transfer':
        $ids      = array($event->account_from, $event->account_to);
        $accounts = Account::whereIn('id', $ids)->get();
        foreach ($accounts as $account) {
          $account->balancedatapoints()->where('date', '>=', $event->date)->delete();
        }
    }

    // flush the cache:
    Cache::flush();
  }

  public function subscribe($events) {
    //$events->listen('user.login', 'UserEventHandler@onUserLogin');
    //$events->listen('user.logout', 'UserEventHandler@onUserLogout');
    //Balancedatapoint::clear(new Carbon('1950-01-01'));
    // deze gaan we op letten:
    //created, updated, saved, deleted
    $events->listen('eloquent.created: *', 'CacheRelatedEventHandler@onEloquentCreated');
    //$events->listen('firefly.home','CacheRelatedEventHandler@onEloquentCreated');
  }

}

$subscriber = new CacheRelatedEventHandler;

Event::subscribe($subscriber);