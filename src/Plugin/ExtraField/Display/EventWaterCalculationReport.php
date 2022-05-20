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


     /* Get one day wash times */

     if (($device_id = $this->getDeviceId($entity))
      && $event_datad = $this->octopusHelper->getWaterCalculationData($device_id,'-1 day')
    ) {
      $calcmonth = 0;
      foreach ($event_datad as $value) {
        $calctime = abs(strtotime($value[datetime]) - strtotime($value[timestop]));
        $calcmday = $calcday + $calctime; 
      }
      $dailytimeuse = date('H:i', $calcday); 

    } 
    else {
      $dailytimeuse = 0;
    }
    $totalflowd = $monthtimeuse * $flowrate;

     /* Get one month wash times */
    if (($device_id = $this->getDeviceId($entity))
      && $event_data = $this->octopusHelper->getWaterCalculationData($device_id,'-1 month')
    ) {
     //$event_data = reset($event_data);
      $calcmonth = 0;
      foreach ($event_data as $value) {
        //cdie($value);
        $calctime = abs(strtotime($value[datetime]) - strtotime($value[timestop]));
        $calcmonth = $calcmonth + $calctime; 
      }

      $monthtimeuse = date('H:i', $calcmonth);
    }
      else {
      $monthtimeuse = 0;
    }
    $totalflowm = $monthtimeuse * $flowrate;
    $flowrate == 0 ? $flowrate = "Not set" : $flowrate; 
      $build['flow_rate'] = [
        '#type' => 'label',
        '#title' => $this->t('Est. Flow Rate: @flow (L/M)', [
          '@flow' => $flowrate,
          '@tflow' => $totalflowd,
        ]),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['watercalc'],
        ],
        '#cache' => ['max-age' => 0],
      ];

     $build['water_calculation'] = [
        '#type' => 'label',
        '#title' => $this->t('Wash Time Today: @water (H:M) | Est. Total Flow Water for Day: @tflowd Litres', [
          '@water' => $dailytimeuse,
          '@tflowd' => $totalflowd,
        ]),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['watercalc'],
        ],
        '#cache' => ['max-age' => 0],
      ];
      $build['monthly_water'] = [
        '#type' => 'label',
        '#title' => $this->t('Wash Time this Month: @monthlyWater (H:M) | Est. Total Flow Month for Day: @tflowm Litres', [
          '@monthlyWater' => $monthtimeuse,
          '@tflowm' => $totalflowm,
        ]),
        '#attributes' => [
          'class' => ['monthlyWater'],
        ],
        '#title_display' => 'before',
        '#cache' => ['max-age' => 0], 
     ];
  

    return $build;
  }

}
