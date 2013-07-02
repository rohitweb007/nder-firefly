<?php
class PageController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function predictionChart() {
    return View::make('pages.prediction');
  }
}