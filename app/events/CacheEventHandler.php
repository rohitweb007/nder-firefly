<?php

class CacheEventHandler {

  public function CRUDTransaction($event) {
    if (!defined('LESSEVENTS')) {
      $class   = strtolower(get_class($event));
      $account = Account::find($event->account_id);
      if ($account) {
        $account->balancedatapoints()->where('date', '>=', $event->date)->delete();
      }
      $this->flushCache();
    }
  }

  public function createdTransfer($event) {
    if (!defined('LESSEVENTS')) {
      $ids      = array($event->account_from, $event->account_to);
      $accounts = Account::whereIn('id', $ids)->get();
      foreach ($accounts as $account) {
        $account->balancedatapoints()->where('date', '>=', $event->date)->delete();
      }
      $this->flushCache();
    }
  }

  private function flushCache() {
    if (!defined('LESSEVENTS')) {
      Cache::flush();
    }
  }

  public function createdAll($event) {
    if (!defined('LESSEVENTS')) {
      Cache::flush();
    }
  }

  public function deletedAll($event) {
    if (!defined('LESSEVENTS')) {
      Cache::flush();
    }
  }

  /**
   * Register the listeners for the subscriber.
   *
   * @param  Illuminate\Events\Dispatcher  $events
   * @return array
   */
  public function subscribe($events) {
    //created, updated, saved, deleted
    $events->listen('eloquent.created: Transaction', 'CacheEventHandler@CRUDTransaction');
    $events->listen('eloquent.deleted: Transaction', 'CacheEventHandler@CRUDTransaction');
    $events->listen('eloquent.updated: Transaction', 'CacheEventHandler@CRUDTransaction');
    $events->listen('eloquent.saved: Transaction', 'CacheEventHandler@CRUDTransaction');

    $events->listen('eloquent.created: Transfer', 'CacheEventHandler@createdTransfer');

    // all
    $events->listen('eloquent.created: *', 'CacheEventHandler@createdAll');
    $events->listen('eloquent.deleted: *', 'CacheEventHandler@deletedAll');
  }

}

$subscriber = new CacheEventHandler;

Event::subscribe($subscriber);