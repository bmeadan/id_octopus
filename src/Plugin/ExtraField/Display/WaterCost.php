<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\id_octopus\Form\WaterCostForm;

/**
 * Provides Event Report.
 *
 * @ExtraFieldDisplay(
 *   id = "water_cost",
 *   label = @Translation("Water Cost"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class WaterCost extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if (($device_id = $this->getDeviceId($entity))) {
      $build['form'] = \Drupal::formBuilder()->getForm(WaterCostForm::class, $device_id);
    }

    return $build;
  }

}
