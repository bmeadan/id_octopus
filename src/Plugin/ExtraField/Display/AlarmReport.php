<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\id_octopus\Form\AlarmReportForm;

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

    if (($device_id = $this->getDeviceId($entity))) {
      $build['form'] = \Drupal::formBuilder()->getForm(AlarmReportForm::class, $device_id);
    }

    return $build;
  }

}
