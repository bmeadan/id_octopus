<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides Start/Stop Action Command buttons.
 *
 * @ExtraFieldDisplay(
 *   id = "startstopdata",
 *   label = @Translation("Start Stop"),
 *   description = @Translation("Start stop info."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class StartStopReport extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
 public function view(ContentEntityInterface $entity) {
    $build = [];
      $tempcolor = 'green';
      $announce = 'Wash in Progress';
      $washcolor = '';
    if (($device_id = $this->getDeviceId($entity))
      && $event_data = $this->octopusHelper->getEventStartStopData($device_id)
    ) {
      $event_data = reset($event_data);
     //cdie($event_data);
      if ($event_data['event_id'] >= 4 && $event_data['event_id'] <= 7) {
        $tempcolor = 'green';  
        $announce = 'Wash in Progress';
      
      } 
      else if ($event_data['event_id'] == 15 || $event_data['event_id'] == 16) {
        $tempcolor = '#f9f195';
        $announce = 'Wash Completed';
        $washcolor = 'washcolor';
      } 
      
      else {
        $tempcolor = '#f3efdd';
        $announce = 'Awaiting Next Wash';
        $washcolor = 'washcolor';
     }
     $build['last_temperature'] = [
        '#type' => 'label',
        '#title' => $this->t('<div class="washstatus"><h2 class="' . $washcolor .'">Wash Status:</h2><div class="washstatusannounce ' . $washcolor . '">' . $announce . '</div></div><style>.washstatus {
            background: conic-gradient(' . $tempcolor .  ' 100%, #f3f3f3 0%
              );
          }
          </style>'),
        '#title_display' => 'before',
        '#attributes' => [
          'class' => ['washstatusrapper'],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }

    return $build;
  }
}
