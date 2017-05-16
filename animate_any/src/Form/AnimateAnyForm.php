<?php

namespace Drupal\animate_any\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AnimateAnyForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animate_any_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

//    $animate_css = libraries_get_path('animate.css') . 'css/animate.css';
//    if (!file_exists($animate_css)) {
//      drupal_set_message(t('animate.css library is missing.'), 'warning');
//    }
//building add more form element to add animation
    $form['#attached']['library'][] = 'animate_any/animate';

    $form['parent_class'] = array(
      '#title' => 'Add Parent Class / ID',
      '#description' => t('You can add body class like <em>body.front (for front page)</em> OR class with dot(.) prefix and Id with hash(#) prefix.'),
      '#type' => 'textfield',
    );

    $form['#tree'] = TRUE;

    $form['animate_fieldset'] = array(
      '#prefix' => '<div id="item-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      '#theme' => 'table',
      '#header' => array(),
      '#rows' => array(),
      '#attributes' => array('class' => 'animation'),
    );

    $field_deltas = $form_state->get('field_deltas');
    if (is_null($field_deltas)) {
      $field_deltas = \NULL;
      $form_state->set('field_deltas', $field_deltas);
    }
    if (!is_null($field_deltas)) {
      $field_count = $field_deltas;
      for ($delta = 0; $delta <= $field_deltas; $delta++) {
        $section_identity = array(
          '#title' => 'Add section class/Id',
          '#description' => t('Add class with dot(.) prefix and Id with hash(#) prefix.'),
          '#type' => 'textfield',
          '#size' => 20,
        );
        $section_animation = array(
          '#title' => 'Select animation',
          '#type' => 'select',
          '#options' => animate_any_options(),
          '#attributes' => array('class' => array('select_animate')),
        );
        $animation = array(
          '#markup' => 'ANIMATE ANY',
          '#prefix' => '<h1 id="animate" class="" style="font-size: 30px;">',
          '#suffix' => '</h1>',
        );

        $remove = array(
          '#type' => 'submit',
          '#value' => t('Remove'),
          '#submit' => array('animate_any_custom_add_more_remove_one'),
          '#ajax' => array(
            'callback' => 'animate_any_custom_remove_callback',
            'wrapper' => 'item-fieldset-wrapper',
          ),
          '#name' => 'remove_name_' . $delta,
        );

        $form['animate_fieldset'][$delta] = array(
          'section_identity' => &$section_identity,
          'section_animation' => &$section_animation,
          'animation' => &$animation,
          'remove' => &$remove,
        );
        $form['animate_fieldset']['#rows'][$delta] = array(
          array('data' => &$section_identity),
          array('data' => &$section_animation),
          array('data' => &$animation),
          array('data' => &$remove),
        );
        unset($section_identity);
        unset($section_animation);
        unset($animation);
        unset($remove);
      }
    }

    $form['instruction'] = array(
      '#markup' => '<strong>Click on <i>Add item</i> button to add animation section.</strong>',
      '#prefix' => '<div class="form-item">',
      '#suffix' => '</div>',
    );
// add more button and callback
    $form['add'] = array(
      '#type' => 'submit',
      '#value' => t('Add Item'),
      '#submit' => array('::animate_any_custom_add_more_add_one'),
      '#ajax' => array(
        'callback' => 'animate_any_custom_add_more_callback',
        'wrapper' => 'item-fieldset-wrapper',
      ),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Settings'),
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    $op = (string) $form_state->getValue('op');
    if ($op == 'Save Settings') {
      $parent = $form_state->getValue('parent_class');
      if (empty($parent)) {
        $form_state->setErrorByName("parent_class", t("Please select parent class"));
      }
      foreach ($form_state->getValue('animate_fieldset') as $key => $value) {
        if (empty($value['section_identity'])) {
          $form_state->setErrorByName("animate_fieldset][{$key}][section_identity", t("Please select section identity for row @key", array('@key' => $key)));
        }
        if ($value['section_animation'] == 'none') {
          $form_state->setErrorByName("animate_fieldset][{$key}][section_animation", t("Please select section animation for row @key", array('@key' => $key)));
        }
      }
    }
  }

  /**
   * Submit for animate_any_settings.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
// encode data in json to store in db
    $op = (string) $form_state->getValue('op');
  }

  /**
   * Implements Add more Callback.
   */
  public function animate_any_custom_add_more_callback(array $form, FormStateInterface $form_state) {
    return $form['animate_fieldset'];
  }

  /**
   * Implements Remove Animate Callback.
   */
  public function animate_any_custom_remove_callback(array $form, FormStateInterface $form_state) {
    return $form['animate_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   */
  public function animate_any_custom_add_more_add_one(array $form, FormStateInterface $form_state) {
    $max = $form_state->get('field_deltas') + 1;
    $error = \Drupal::logger('animate_any')->error($max);
    $form_state->set('field_deltas', $max);
    $form_state->setRebuild();
  }

  public function animate_any_custom_add_more_remove_one(array $form, FormStateInterface $form_state) {
    $delta_remove = $form_state->getValue('triggering_element', array('#parents'));
    $k = array_search($delta_remove, $form_state->getValue('field_deltas'));
    unset($form_state['field_deltas'][$k]);
    $form_state->setRebuild();
  }

}

