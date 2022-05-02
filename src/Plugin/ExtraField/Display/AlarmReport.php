<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides Alarm Report.
 *
 * @ExtraFieldDisplay(
 *   id = "alarm_report",
 *   label = @Translation("Alarm Report"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class AlarmReport extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if (($device_id = $this->getDeviceId($entity))
      && $alarm_data = $this->octopusHelper->getAlarmReport($device_id)
    ) {
      $build['alarm_data'] = [
        '#type' => 'table',
        '#caption' => $this->t('Alarms'),
        '#header' => [
          $this->t('Alarm'),
          $this->t('Date'),
        ],
        '#rows' => $alarm_data,
        '#attributes' => [
          'class' => ['alarm-report-item'],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }

    return $build;
  }

}
