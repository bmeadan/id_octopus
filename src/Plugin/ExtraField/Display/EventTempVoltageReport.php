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
      $build['last_temperature'] = [
        '#type' => 'label',
        '#title' => $this->t('Temperature: @temperature C', [
          '@temperature' => $event_data['temperature'],
        ]),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['temperature'],
        ],
        '#cache' => ['max-age' => 0],
      ];
      $build['last_voltage'] = [
        '#type' => 'label',
        '#title' => $this->t('Voltage: @voltage V', [
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
