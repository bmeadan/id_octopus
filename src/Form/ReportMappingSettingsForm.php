<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides form to configure Alarm/Event report mapping.
 */
class ReportMappingSettingsForm extends ConfigFormBase {

  /**
   * Settings name.
   */
  public const SETTINGS_NAME = 'id_octopus.alarm_event_report_mapping_settings';

  /**
   * Types of Events.
   *
   * @var array
   */
  public array $types = ['alarm', 'event'];

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::SETTINGS_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alarm_event_report_mapping_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#tree'] = TRUE;

    $form_wrapper_id = 'alarm_event_report_mapping_settings_form-wrapper';
    $form['#prefix'] = '<div id="' . $form_wrapper_id . '">';
    $form['#suffix'] = '</div>';

    $config = $this->config(self::SETTINGS_NAME);

    foreach ($this->types as $item) {
      $key = $item . '_wrapper';
      $input = $form_state->getUserInput();
      $data = $input[$key]['options'] ?? ($config->get($item) ?? []);

      $form[$key] = [
        '#type' => 'details',
        '#title' => $this->t('@type report mapping', [
          '@type' => ucfirst($item),
        ]),
        '#open' => TRUE,
      ];
      $form[$key]['options'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Database value'),
          $this->t('Label'),
        ],
      ];

      $rows_number = count($data);
      for ($i = 0; $i < $rows_number; $i++) {
        $form[$key]['options'][$i] = [
          'db_value' => [
            '#type' => 'textfield',
            '#default_value' => $data[$i]['db_value'] ?? '',
          ],
          'label_value' => [
            '#type' => 'textfield',
            '#default_value' => $data[$i]['label_value'] ?? '',
          ],
        ];
      }

      $form[$key]['add_row'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add row'),
        '#name' => $item,
        '#submit' => ['::addRow'],
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => $form_wrapper_id,
          'progress' => ['type' => 'fullscreen'],
        ],
      ];
    }

    return $form;
  }

  /**
   * Handle Add row action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addRow(array $form, FormStateInterface $form_state): void {
    $triggering_name = $form_state->getTriggeringElement()['#name'] ?? NULL;
    $input = $form_state->getUserInput();
    $input[$triggering_name . '_wrapper']['options'][] = [];
    $form_state->setUserInput($input);
    $form_state->setRebuild();
  }

  /**
   * Return structured Form array.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Structured Form array.
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    $config = $this->config(self::SETTINGS_NAME);
    foreach ($this->types as $item) {
      if ($options = $form_values[$item . '_wrapper']['options'] ?? NULL) {
        foreach ($options as $key => $values) {
          if (!array_filter($values)) {
            unset($options[$key]);
          }
        }
        $config->set($item, array_values($options));
      }
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
