<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\id_octopus\Form\EventReportForm;

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

    if (($device_id = $this->getDeviceId($entity))) {
      $build['form'] = \Drupal::formBuilder()->getForm(EventReportForm::class, $device_id);
    }

    return $build;
  }

}
