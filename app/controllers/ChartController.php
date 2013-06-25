<?php

class ChartController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showOverExpendingCategories() {
    $categories = Auth::user()->categories()->get();
    $data = array(
        'cols' => array(
            array(
                'id'    => 'Category',
                'label' => 'Category',
                'type'  => 'string',
            ),

            array(
                'id'    => 'avg',
                'label' => 'Spent too much',
                'type'  => 'number',
            ),
            array(
                'id'    => 'spent',
                'label' => 'Spent so far',
                'type'  => 'number',
            ),
            array(
                'id'    => 'judgement',
                'label' => 'Judgement',
                'type'  => 'string',
            ),
            array(
                'id'    => 'spentmore',
                'label' => 'Spent on average',
                'type'  => 'number',
            ),
//            array(
//                'id'    => 'overspent',
//                'label' => 'Spent too much',
//                'type'  => 'number',
//            ),
        ),
        'rows' => array()
    );
    $collection = array();

    foreach($categories as $category) {



      $avg_spent = $category->averagespending();
      $spent     = $category->spent();
      $category->name = Crypt::decrypt($category->name);
      if ($avg_spent > 0) {
        if ($avg_spent < $spent) {
          $current = array();





          // overspent as part of average:
          //100-(100/120)*100
          $spentpct = 100-(($avg_spent / $spent)*100);
          $spentindex = round($spentpct / 10,0);
          if($spentindex == 0) {
            $descr = 'Overspent < 10%';
          } else {
            $descr = 'Overspent ~'.($spentindex*10).'%';
          }

          // 0: Naam van bolletje.
          // 1: Verticale as (hoger is hoger)
          // 2: Horizontale as (hoger is verder naar rechts
          // 3: Kleur(groep)
          // 4: grootte van bolletje.

          $current['c'][0]['v'] = $category->name;
          $current['c'][1]['v'] = $spent - $avg_spent;
          $current['c'][2]['v'] = $spent;
          $current['c'][3]['v'] = $descr;
          $current['c'][4]['v'] = $avg_spent;


          $current['spentindex'] = $spentindex;
          $collection[] = $current;
        }
      }
    }
    $tmp = array();
    foreach($collection as &$ma) {
       $tmp[] = &$ma['spentindex'];
    }
    array_multisort($tmp,$collection);
    $index = 0;
    foreach($collection as $c) {
      $data['rows'][$index]['c'] = $c['c'];
      $index++;
    }
    return Response::json($data);

  }
}