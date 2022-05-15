<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Build Event report.
 */
class EventReportForm extends ReportFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_report_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($args = $form_state->getBuildInfo()['args'] ?? NULL) {
      $wrapper = 'event-report-form-wrapper';
      $form['#prefix'] = '<div id="' . $wrapper . '">';
      $form['#suffix'] = '</div>';

      $alarm_data = $this->octopusHelper->getEventReport(reset($args), $form_state->getUserInput());
      $form['event_filters'] = [
        '#type' => 'container',
        '#attributes' => ['class' => 'event-report-filters-wrapper'],
      ];
      $form['event_filters']['event_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Event type'),
        '#options' => ['_none' => $this->t('All')] + $this->octopusHelper->getEventLabels(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $wrapper,
        ],
      ];

      $form['event_filters']['event_timeframe'] = [
        '#type' => 'select',
        '#title' => $this->t('Timeframe'),
        '#options' => $this->octopusHelper->getTimeframeOptions(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $wrapper,
        ],
      ];

      $form['event_filters']['event_sort'] = [
        '#type' => 'select',
        '#title' => $this->t('Sort by'),
        '#options' => $this->getSortOptions(),
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $wrapper,
        ],
      ];
      $form['event_data'] = [
        '#type' => 'table',
        '#caption' => $this->t('Event Report'),
        '#header' => [$this->t('Event Type'), $this->t('Date')],
        '#rows' => $alarm_data,
        '#empty' => $this->t('No data found, change filter options.'),
        '#attributes' => [
          'class' => ['event-report-item'],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }

    return $form;
  }

}
