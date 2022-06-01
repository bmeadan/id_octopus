<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides Water Calculation report.
 *
 * @ExtraFieldDisplay(
 *   id = "event_water_calculation_report",
 *   label = @Translation("Event Water Calculation Report"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class EventWaterCalculationReport extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];


      $flow_rate = $entity->get('field_flow_rate')->getValue();
      $flowrate = isset($flow_rate[0]['value']) ? $flow_rate[0]['value'] : 0;
      $water_cost = $entity->get('field_water_cost')->getValue();
      $cost = isset($water_cost[0]['value']) ? $water_cost[0]['value'] : 0;
      $user = \Drupal::currentUser();
      $user_id = \Drupal::currentUser()->id();
      $user = \Drupal\user\Entity\User::load($user_id);
      $currency = $user->get('field_currency')->value;
      if (empty($currency)) { $currency = '$';}

     /* Get one day wash times */
     $calcday = 0;
     if (($device_id = $this->getDeviceId($entity))
      && $event_datad = $this->octopusHelper->getWaterCalculationData($device_id,'-1 day')
    ) {
      $calcday = 0;
      foreach ($event_datad as $value) {
        $calctime = abs(strtotime($value['start_time']) - strtotime($value['stop_time']));
        $calcday = $calcday + $calctime; 
      }
      $dailytimeuse = date('H:i', $calcday); 
    } 
    else {
      $dailytimeuse = 0;
    }
    $totalflowd = (round($calcday/60/60,2)) * $flowrate;
    // if changing to m3/hr change to: $totalflowd = ($calcday/(24*60)) * $flowrate;
    $costd = $cost * $totalflowd;
    $cost == 0 ? $cost = "Not set" : $cost;



     /* Get one month wash times */
    if (($device_id = $this->getDeviceId($entity))
      && $event_data = $this->octopusHelper->getWaterCalculationData($device_id,'-1 month')
    ) {
      $calcmonth = 0;
      foreach ($event_data as $value) {
        $calctime = abs(strtotime($value['start_time']) - strtotime($value['stop_time']));
        $calcmonth = $calcmonth + $calctime; 
      }
      $monthtimeuse = date('H:i', $calcmonth);
    }
      else {
      $monthtimeuse = 0;
    }
    $totalflowm = (round($calcmonth/60/60,2)) * $flowrate;
    // if changing to m3/hr change to: $totalflowm = ($calcmonth/(24*60)) * $flowrate;

    $flowrate == 0 ? $flowrate = "Not set" : $flowrate; 
    $costm = $cost * $totalflowm;
    $cost == 0 ? $cost = "Not set" : $cost;

   /* Create wash times graph for day */
    if (($device_id = $this->getDeviceId($entity))
      && $event_data = $this->octopusHelper->getWaterCalculationData($device_id,'-1 month')
    ) {
      $i = 0;
     $durline = '<figure><ul class="sparklist"><li><span class="sparkline">';
      foreach ($event_data as $value) {
         $dur = explode(':',$value['duration']);
           if (count($dur) === 3) {
            $duration = ($dur[0] * 3600 + $dur[1] * 60 + $dur[2]);
            $durdata = ' <span class="index"><span class="count" style="height: ' . $duration /5 . '%;">' . $duration . ',</span></span>';
          }
          $durline .= $durdata;
          $i++;          
          //cdie($duration);
      }
      $durline .= '</span></li>';
    }
      
    

      $build['flow_rate'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="boxheading">Est. Flow Rate:</div> @flow m<sup>3</sup>/hour<div class="flowlink"><a href="#flowrateform">Change Flow Rate</a></div>', [
          '@flow' => round($flowrate,2),
        ]),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['flowratereading'],
        ],
        '#cache' => ['max-age' => 0],
      ];

     $build['water_calculation'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="boxheading">Wash Time Today:</div> @water (H:M) <br> <div class="boxheading2">Est. Daily Water Use:</div> @tflowd m<sup>3</sup> <div class="costitem">Estimated Cost Today: ' . $currency . '@cost </div>', [
          '@water' => $dailytimeuse,
          '@tflowd' => $totalflowd,
          '@cost' => $costd,
        ]),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['watercalc'],
        ],
        '#cache' => ['max-age' => 0],
      ];
      $build['monthly_water'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="boxheading">Monthly Wash Time:</div> @monthlyWater (H:M) <br> <div class="boxheading2">Est. Month Water Use:</div> @tflowm m<sup>3</sup> <div class="costitem">Estimated Monthly Cost: ' . $currency . '@costm </div>', [
          '@monthlyWater' => $monthtimeuse,
          '@tflowm' => $totalflowm,
          '@costm' => $costm,
        ]),
        '#attributes' => [
          'class' => ['monthlyWater'],
        ],
        '#title_display' => 'before',
        '#cache' => ['max-age' => 0], 
     ];
   /*$build['dailygraph'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="boxheading">Graph:</div> ' . $durline . ' </ul></figure><div class="graphtable"></div>'),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['graphtable'],
        ],
        '#cache' => ['max-age' => 0],
      ];*/

    return $build;
  }


}
