<?php

class ChartController extends BaseController {

  public function __construct() {
    $this->beforeFilter('gs'); // do Google "sync".
  }

  public function showOverExpendingCategories() {
    $key = cacheKey('overExpending');
    if (Cache::has($key)) {
      return Response::json(Cache::get($key));
    } else {
      $categories = Auth::user()->categories()->get();
      $data       = array(
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

      foreach ($categories as $category) {



        $avg_spent      = $category->averagespending();
        $spent          = $category->spent();
        $category->name = Crypt::decrypt($category->name);
        if ($avg_spent > 0) {
          if ($avg_spent < $spent) {
            $current = array();





            // overspent as part of average:
            //100-(100/120)*100
            $spentpct   = 100 - (($avg_spent / $spent) * 100);
            $spentindex = round($spentpct / 10, 0);
            if ($spentindex == 0) {
              $descr = 'Overspent < 10%';
            } else {
              $descr = 'Overspent ~' . ($spentindex * 10) . '%';
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
            $collection[]          = $current;
          }
        }
      }
      $tmp = array();
      foreach ($collection as &$ma) {
        $tmp[] = &$ma['spentindex'];
      }
      array_multisort($tmp, $collection);
      $index = 0;
      foreach ($collection as $c) {
        $data['rows'][$index]['c'] = $c['c'];
        $index++;
      }
      Cache::put($key, $data, 1440);
    }
    return Response::json($data);
  }

  public function showCategoriesByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return Response::error(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $categories = Auth::user()->categories()->get();
      $records    = array();
      foreach ($categories as $cat) {
        $cat->name = Crypt::decrypt($cat->name);
        // find out the expenses for each category:
        $trans     = floatval($cat->transactions()->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $transf    = floatval($cat->transfers()->where('account_from', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
        $transf += floatval($cat->transfers()->where('account_to', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $records[] = array(
            'category' => $cat->name,
            'spent'    => $trans,
            'moved'    => $transf
        );
      }
      // everything *outside* of the categories:
      $trans  = floatval($account->transactions()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      $transf = floatval($account->transfersfrom()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $transf += floatval($account->transfersto()->whereNull('category_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));


      array_unshift($records, array(
          'category' => 'Outside of categories',
          'spent'    => $trans,
          'moved'    => $transf
      ));


      // klopt wie ein busje!
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'cat',
                  'label' => 'Category',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'amount',
                  'label' => 'Earned',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'moved',
                  'label' => 'Moved',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($records as $r) {
        if (!($r['spent'] == 0 && $r['moved'] == 0)) {
          $data['rows'][$index]['c'][0]['v'] = $r['category'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['moved'];
          $index++;
        }
      }
      return Response::json($data);
    }
  }

  public function showBudgetsByAccount($id) {
    $account = Auth::user()->accounts()->find($id);
    if (is_null(Input::get('start')) || is_null(Input::get('end')) || is_null($account)) {
      return Response::error(404);
    } else {
      $start = new DateTime(Input::get('start'));
      $end   = new DateTime(Input::get('end'));

      $start_first = clone $start;
      $end_first   = clone $end;
      $start_first->modify('first day of this month');
      $end_first->modify('first day of this month');


      // all budgets + stuff outside budgets should match this!
      $budgets = Auth::user()->budgets()->orderBy('date', 'DESC')->orderBy('amount', 'DESC')->where('date', '>=', $start_first->format('Y-m-d'))->where('date', '<=', $end_first->format('Y-m-d'))->get();
      $records = array();
      foreach ($budgets as $budget) {
        $budget->name = Crypt::decrypt($budget->name);
        $date         = new DateTime($budget->date);
        // find out the expenses for each budget:
        $trans        = floatval($budget->transactions()->where('account_id', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $transf       = floatval($budget->transfers()->where('account_from', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
        $transf += floatval($budget->transfers()->where('account_to', '=', $account->id)->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
        $records[]    = array(
            'budget' => $budget->name . ' (' . $date->format('F Y') . ')',
            'spent'  => $trans,
            'moved'  => $transf
        );
      }
      // everything *outside* of the budgets:
      $trans  = floatval($account->transactions()->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));
      $transf = floatval($account->transfersfrom()->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount')) * -1;
      $transf += floatval($account->transfersto()->whereNull('budget_id')->where('date', '>=', $start->format('Y-m-d'))->where('date', '<=', $end->format('Y-m-d'))->sum('amount'));




      array_unshift($records, array(
          'budget' => 'Outside of budgets',
          'spent'  => $trans,
          'moved'  => $transf
      ));



      // klopt wie ein busje!
      $data = array(
          'cols' => array(
              array(
                  'id'    => 'budget',
                  'label' => 'Budget',
                  'type'  => 'string',
              ),
              array(
                  'id'    => 'amount',
                  'label' => 'Earned',
                  'type'  => 'number',
              ),
              array(
                  'id'    => 'moved',
                  'label' => 'Moved',
                  'type'  => 'number',
              ),
          ),
          'rows' => array()
      );

      $index = 0;
      foreach ($records as $r) {
        if (!($r['spent'] == 0 && $r['moved'] == 0)) {
          $data['rows'][$index]['c'][0]['v'] = $r['budget'];
          $data['rows'][$index]['c'][1]['v'] = $r['spent'];
          $data['rows'][$index]['c'][2]['v'] = $r['moved'];
          $index++;
        }
      }
    }



    return Response::json($data);
  }

}