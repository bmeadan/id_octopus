<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\id_octopus\OctopusHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form with Timeframe options and a chart.
 */
class PressureChartTimeframeForm extends FormBase {

  /**
   * Octopus helper service definition.
   *
   * @var \Drupal\id_octopus\OctopusHelper
   */
  protected OctopusHelper $octopusHelper;

  /**
   * Charts global settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $chartsConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('id_octopus.octopus_helper'),
      $container->get('config.factory')
    );
  }

  /**
   * PressureChartTimeframeForm constructor.
   *
   * @param \Drupal\id_octopus\OctopusHelper $octopus_helper
   *   Octopus helper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   */
  public function __construct(OctopusHelper $octopus_helper, ConfigFactoryInterface $config_factory) {
    $this->octopusHelper = $octopus_helper;
    // All additional Chart global settings can be taken from here.
    $this->chartsConfig = $config_factory->get('charts.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'id_octopus_pressure_chart_timeframe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($args = $form_state->getBuildInfo()['args'] ?? NULL) {
      $form['#prefix'] = '<div id="pressure-chart-form-wrapper">';
      $form['#suffix'] = '</div>';

      [$device_id, $settings] = $args;

      $form['timeframe'] = [
        '#type' => 'select',
        '#title' => $this->t('Timeframe'),
        '#options' => $this->octopusHelper->getTimeframeOptions(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'pressure-chart-form-wrapper',
        ],
      ];

      // Default timeframe on load.
      $timeframe = $form_state->getUserInput()['timeframe'] ?? array_key_first($form['timeframe']['#options']);
      $show_alarms = $form_state->getUserInput()['show_alerts'] ?? FALSE;

      $alarm_data = $show_alarms ? $this->octopusHelper->getAlarmReportData($device_id, ['alarm_timeframe' => $timeframe]) : [];

      $form['show_alerts'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show Alarms'),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'pressure-chart-form-wrapper',
        ],
        '#default_value' => 1,        
      ];

      $event_data = $this->octopusHelper->getEventPressureData($device_id, $timeframe);

      $form['chart'] = $this->buildChart($event_data, $alarm_data, $settings);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No need to do anything here.
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
   * Helper function to build the Chart element.
   *
   * @param $event_data
   *   Array with data from database.
   * @param $settings
   *   Array with extra field display settings.
   *
   * @return array
   *   Chart build.
   */
  public function buildChart(array $event_data, array $alarm_data, array $settings): array {
    $left_pressure = [
      '#type' => 'chart_data',
      '#title' => $this->t('Left'),
      '#data' => array_column($event_data, 'left_pressure'),
      '#color' => $settings['left_pressure_color'],
    ];
    $right_pressure = [
      '#type' => 'chart_data',
      '#title' => $this->t('Right'),
      '#data' => array_column($event_data, 'right_pressure'),
      '#color' => $settings['right_pressure_color'],
    ];

    $alarms = [];
    // Get max value to print Alarms higher on Chert.
    $max = max(max($left_pressure['#data']), max($right_pressure['#data'])) + 1;

    $date_labels = array_column($event_data, 'datetime');
    foreach ($date_labels as $i => &$value) {
      $value = (new DrupalDateTime($value))->format($settings['date_format']);
      foreach ($alarm_data as $k => $alarm) {
        $date = (new DrupalDateTime($alarm['datetime']))->format($settings['date_format']);
        if ($date === $value) {
          $alarms[$i] = $max;
          unset($alarm_data[$k]);
          break;
        }
        // Set empty value to keep order of values by keys.
        $alarms[$i] = NULL;
      }
    }

    $build = [
      '#type' => 'chart',
      '#title' => $this->t('Pressure Report'),
      '#chart_type' => $settings['chart_type'],
      'left_pressure' => $left_pressure,
      'right_pressure' => $right_pressure,
      'x_axis' => [
        '#type' => 'chart_xaxis',
        '#title' => $this->t('Date'),
        '#labels' => $date_labels,
        '#labels_rotation' => $this->chartsConfig
          ->get('charts_default_settings.xaxis.labels_rotation'),
      ],
      'y_axis' => [
        '#type' => 'chart_yaxis',
        '#title' => $this->t('Bar'),
        '#labels_rotation' => $this->chartsConfig
          ->get('charts_default_settings.yaxis.labels_rotation'),
      ],
      '#cache' => ['max-age' => 0],
    ];

    if ($alarms) {
      $alarm = [
        '#type' => 'chart_data',
        '#title' => $this->t('Alarms'),
        '#data' => $alarms,
        '#color' => $settings['alarm_color'],
        '#chart_type' => 'bubble',
      ];

      $build['alarm'] = $alarm;
    }

    return $build;
  }

}
