<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides Event Report.
 *
 * @ExtraFieldDisplay(
 *   id = "event_report",
 *   label = @Translation("Event Report"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class EventReport extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if (($device_id = $this->getDeviceId($entity))
      && $event_data = $this->octopusHelper->getEventReport($device_id)
    ) {
      $header =  [
      ['data' => t('Event')], 
      ['data' => t('Date'), 'sort' => 'desc'],
    ];
        
      $build['alarm_data'] = [
        '#type' => 'table',
        '#caption' => $this->t('Events'),
        '#header' => $header,
        '#rows' => $event_data,
        '#attributes' => [
          'class' => ['event-report-item'],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }

    return $build;
  }

}
