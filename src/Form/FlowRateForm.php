<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\Entity\Node;
/**
 * Build Event report.
 */
class FlowRateForm extends ReportFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'flow_rate_form';
  }
  public function getflowrate(ContentEntityInterface $entity) {
    $flow_rate = $entity->get('field_flow_rate')->getValue();
    $flowrate = isset($flow_rate[0]['value']) ? $flow_rate[0]['value'] : 0;
    return $flowrate;
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($args = $form_state->getBuildInfo()['args'] ?? NULL) {

      $form['#prefix'] = '<div id="flow_rate_wrapper">';
      $form['#suffix'] = '</div>';
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node instanceof \Drupal\node\NodeInterface) {
        if ($node->hasField('field_flow_rate') && !$node->get('field_flow_rate')->isEmpty()) {
          $flow_rate = $node->get('field_flow_rate')->value;
        }
      }

      $form['flow_rate'] = [
        '#type' => 'textfield',
        '#default_value' => $flow_rate,
        '#title' => $this->t('<div id="flowrateform"></div>The current flow rate is set at '. $flow_rate . 'm<sup>3</sup>/hour. You may change it here.'),
        '#attributes' => [
          'class' => ['flow-rate-item'],

        ],
        '#cache' => ['max-age' => 0],
      ];

      $form['nid'] = [
        '#type' => 'hidden',
        '#value' => $node->id(),
      ];


      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ];

      return $form;
    }


  }


/**
   * Validate the title and the checkbox of the form
   * 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * 
   */
public function validateForm(array &$form, FormStateInterface $form_state) {
  parent::validateForm($form, $form_state);



}
  /**
 * {@inheritdoc}
 */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $flow_rate = $form_state->getValue('flow_rate');
    //$form_state->setValue('field_form_value', '23');
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $form_state->getValue('nid');
      $node = Node::load($nid);
      $node->set('field_flow_rate',$flow_rate ); 
      $node->save();
    }
    
    
  }

}
