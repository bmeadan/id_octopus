<?php

namespace Drupal\id_octopus\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Build Event report.
 */
class WaterCostForm extends ReportFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'water_cost_form';
  }
  public function getwatercost(ContentEntityInterface $entity) {
    $water_cost = $entity->get('field_water_cost')->getValue();
    $watercost = isset($water_cost[0]['value']) ? $water_cost[0]['value'] : 0;
    return $watercost;
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($args = $form_state->getBuildInfo()['args'] ?? NULL) {

      $form['#prefix'] = '<div id="water_cost_wrapper">';
      $form['#suffix'] = '</div>';
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node instanceof \Drupal\node\NodeInterface) {
        if ($node->hasField('field_water_cost') && !$node->get('field_water_cost')->isEmpty()) {
          $water_cost = $node->get('field_water_cost')->value;
          $user = \Drupal::currentUser();
          $user_id = \Drupal::currentUser()->id();
          $user = \Drupal\user\Entity\User::load($user_id);
          $currency = $user->get('field_currency')->value;
        
        }
      }

      $form['water_cost'] = [
        '#type' => 'textfield',
        '#default_value' => $water_cost,
        '#title' => $this->t('<div id="watercostform"></div>The current water cost is set at ' . $currency . ''. $water_cost . ' per m<sup>3</sup>/hour. You may change it here.'),
        '#attributes' => [
          'class' => ['water-cost-item'],

        ],
        '#cache' => ['max-age' => 0],
        '#description' => '<a href=/user/' . $user_id . '/edit> Change Currency</a>',
      ];

      $form['nid'] = [
        '#type' => 'hidden',
        '#value' => $node->id(),
      ];


      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Change Cost'),
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
    $water_cost = $form_state->getValue('water_cost');
    //$form_state->setValue('field_form_value', '23');
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $form_state->getValue('nid');
      $node = Node::load($nid);
      $node->set('field_water_cost',$water_cost ); 
      $node->save();
    }
    
    
  }

}
