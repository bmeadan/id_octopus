<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides Temperature and Voltage report.
 *
 * @ExtraFieldDisplay(
 *   id = "event_temp_voltage_report",
 *   label = @Translation("Event Temp, Voltage Report"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class EventTempVoltageReport extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if (($device_id = $this->getDeviceId($entity))
      && $event_data = $this->octopusHelper->getEventTempVoltageData($device_id)
    ) {
      $event_data = reset($event_data);
      $tempcolor = $event_data['temperature'] <= 10 ? 'blue' : 'orange';
      $tempcolor = $event_data['temperature'] >= 30 ? 'red' : 'orange';
      $build['last_temperature'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="boxheading">Temperature:</div> @temperature C <br><div class="dot"></div><div class="tempbar"></div><br><style>.dot {
            background: conic-gradient(' . $tempcolor .  ' 100%, #f3f3f3 0%
              );
          }
          .tempbar {
            background: linear-gradient(90deg, ' . $tempcolor .  '  ' . $event_data['temperature']*2 . '%, #00FFFF 0%);
          }
          </style>', [
          '@temperature' => $event_data['temperature'],
        ]),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['temperature'],
        ],
        '#cache' => ['max-age' => 0],
      ];
      if ($event_data['voltage'] < 8) {
         $voltcolor = "red";
         $voltwarning = '<div class="lowbattery">Low Battery!</div>'; 
      }
      else {
         $voltcolor = "green";
      }
      $build['last_voltage'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="boxheading">Voltage:</div> @voltage V ' . $voltwarning . '<div class="voltbartext">' . round($event_data['voltage']/12*100,1) . '%</div><div class="voltbar"></div><style>.voltbar {
            background: linear-gradient(90deg, ' . $voltcolor . ' ' . $event_data['voltage']/12*100 . '%, #00FFFF 0%);
          }
          
          }</style>', [
          '@voltage' => $event_data['voltage'],
        ]),
        '#attributes' => [
          'class' => ['voltage'],
        ],
        '#title_display' => 'before',
        '#cache' => ['max-age' => 0], 
     ];
    }

    return $build;
  }

}
