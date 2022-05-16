<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\id_octopus\OctopusHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Alarm/Event reports.
 */
abstract class ReportFormBase extends FormBase {

  /**
   * Octopus helper service definition.
   *
   * @var \Drupal\id_octopus\OctopusHelper
   */
  protected OctopusHelper $octopusHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('id_octopus.octopus_helper')
    );
  }

  /**
   * PressureChartTimeframeForm constructor.
   *
   * @param \Drupal\id_octopus\OctopusHelper $octopus_helper
   *   Octopus helper service.
   */
  public function __construct(OctopusHelper $octopus_helper) {
    $this->octopusHelper = $octopus_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  /**
   * Form Ajax Callback handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Structured form array.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * Helper function to get Sort options.
   *
   * Field to sort by and order to de exploded.
   *
   * @return array
   *   Sort options list.
   */
  public function getSortOptions(): array {
    return [
      'datetime__desc' => $this->t('Date (DESC)'),
      'datetime__asc' => $this->t('Date (ASC)'),
      'event_id__desc' => $this->t('Alarm type (DESC)'),
      'event_id__asc' => $this->t('Alarm type (ASC)'),
    ];
  }

}
