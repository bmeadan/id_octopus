<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Build Alarm report.
 */
class AlarmReportForm extends ReportFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alarm_report_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($args = $form_state->getBuildInfo()['args'] ?? NULL) {
      $wrapper = 'alarm-report-form-wrapper';
      $form['#prefix'] = '<div id="' . $wrapper . '">';
      $form['#suffix'] = '</div>';

      $alarm_data = $this->octopusHelper->getAlarmReport(reset($args), $form_state->getUserInput());
      $form['alarm_filters'] = [
        '#type' => 'container',
        '#attributes' => ['class' => 'alarm-report-filters-wrapper'],
      ];
      $form['alarm_filters']['alarm_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Alarm type'),
        '#options' => ['_none' => $this->t('All')] + $this->octopusHelper->getAlarmLabels(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $wrapper,
        ],
      ];

      $form['alarm_filters']['alarm_timeframe'] = [
        '#type' => 'select',
        '#title' => $this->t('Timeframe'),
        '#options' => $this->octopusHelper->getTimeframeOptions(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $wrapper,
        ],
      ];

      $form['alarm_filters']['alarm_sort'] = [
        '#type' => 'select',
        '#title' => $this->t('Sort by'),
        '#options' => $this->getSortOptions(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $wrapper,
        ],
      ];
      $form['alarm_data'] = [
        '#type' => 'table',
        '#caption' => $this->t('Alarm Report'),
        '#header' => [$this->t('Alarm Type'), $this->t('Date')],
        '#rows' => $alarm_data,
        '#empty' => $this->t('No data found, change filter options.'),
        '#attributes' => [
          'class' => ['alarm-report-item'],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }

    return $form;
  }

}
