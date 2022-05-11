<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;

/**
 * Provides Start/Stop Action Command buttons.
 *
 * @ExtraFieldDisplay(
 *   id = "action_buttons",
 *   label = @Translation("Action Buttons"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class ActionButtons extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if ($device_id = $this->getDeviceId($entity)) {
      foreach (['start', 'stop'] as $command) {
        $build[$command] = [
          '#type' => 'link',
          '#title' => $command === 'start' ? $this->t('Start Wash') : $this->t('Stop Wash'),
          '#url' => Url::fromRoute('id_octopus.send_command.' . $command, [
            'device_id' => $device_id,
          ]),
          '#attributes' => [
            'class' => ['use-ajax', 'button', 'command-' . $command],
          ],
        ];
      }
    }

    return $build;
  }

}
