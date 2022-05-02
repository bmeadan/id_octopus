<?php

namespace Drupal\id_octopus\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\id_octopus\Form\PressureChartTimeframeForm;

/**
 * Provides Pressure diagram chart.
 *
 * @ExtraFieldDisplay(
 *   id = "event_pressure_chart",
 *   label = @Translation("Event Pressure Chart"),
 *   description = @Translation("An extra field that uses dependency injection."),
 *   bundles = {
 *     "node.device",
 *   }
 * )
 */
class EventPressureChart extends DeviceReportBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $build = [];

    if (($device_id = $this->getDeviceId($entity))) {
      $settings = $this->getSettings();
      $build['form'] = \Drupal::formBuilder()->getForm(PressureChartTimeframeForm::class, $device_id, $settings);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $chart_types = ['area', 'bar', 'column', 'line', 'pie', 'spline'];
    $form['chart_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart Type'),
      '#options' => array_combine($chart_types, $chart_types),
    ];

    $form['left_pressure_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Left Pressure color'),
    ];

    $form['right_pressure_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Right Pressure color'),
    ];

    $form['date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFormValues() {
    $values = parent::defaultFormValues();

    $values += [
      'chart_type' => 'spline',
      'left_pressure_color' => '#1d84c3',
      'right_pressure_color' => '#77b259',
      'date_format' => 'Y-m-d',
    ];

    return $values;
  }

}
