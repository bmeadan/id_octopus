<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\id_octopus\Form\FlowRateForm;

/**
 * Provides Event Report.
 *
 * @ExtraFieldDisplay(
 *   id = "flow_rate",
 *   label = @Translation("Flow Rate"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class FlowRate extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if (($device_id = $this->getDeviceId($entity))) {
      $build['form'] = \Drupal::formBuilder()->getForm(FlowRateForm::class, $device_id);
    }

    return $build;
  }

}
