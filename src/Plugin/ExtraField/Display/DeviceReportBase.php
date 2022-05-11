<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field_plus\Plugin\ExtraFieldPlusDisplayBase;
use Drupal\id_octopus\OctopusHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Device report with service injection.
 */
abstract class DeviceReportBase extends ExtraFieldPlusDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * Octopus helper service definition.
   *
   * @var \Drupal\id_octopus\OctopusHelper
   */
  protected OctopusHelper $octopusHelper;

  /**
   * Constructs a ExtraFieldDisplayFormattedBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\id_octopus\OctopusHelper $octopus_helper
   *   Octopus helper service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OctopusHelper $octopus_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->octopusHelper = $octopus_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('id_octopus.octopus_helper')
    );
  }

  /**
   * Helper function to get Device ID (MAC).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Device entity.
   *
   * @return string|null
   *   Device ID if present, Null otherwise.
   */
  protected function getDeviceId(EntityInterface $entity): ?string {
    $device_id = NULL;
    if ($entity->hasField('field_hash')
      && !$entity->get('field_hash')->isEmpty()) {
      $device_id = $entity->get('field_hash')->value;
    }
    return $device_id;
  }

}
