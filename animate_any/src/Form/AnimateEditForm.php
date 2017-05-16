<?php

namespace Drupal\animate_any\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AnimateEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animate_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $element = NULL) {

    $fetch = \Drupal::database()->select("animate", "a");
    $fetch->fields('a');
    $fetch->condition('a.aid', $element);
    $fetch_results = $fetch->execute()->fetchAssoc();

    $form = array();
    $form['#attached']['library'][] = 'animate_any/animate';
    
    $form['#tree'] = TRUE;
    $form['parent_class'] = array(
      '#title' => 'Add Parent Class',
      '#description' => t('You can add body class like <em>body.front (for front page)</em> OR class with dot(.) prefix and Id with hash(#) prefix.'),
      '#type' => 'textfield',
      '#default_value' => $fetch_results['parent'],
    );
    $form['aid'] = array(
      '#type' => 'hidden',
      '#default_value' => $element,
    );
    $form['animate_fieldset'] = array(
      '#prefix' => '<div id="item-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      '#theme' => 'table',
      '#header' => array(),
      '#rows' => array(),
      '#attributes' => array('class' => 'animation'),
    );
    // json decode to get json to array
    $data = json_decode($fetch_results['identifier']);
    foreach ($data as $key => $value) {
      $section_identity = array(
        '#type' => 'textfield',
        '#title' => t('Section identity'),
        '#default_value' => $value->section_identity,
        '#description' => t("Add class with dot(.) prefix and Id with hash(#) prefix."),
      );
      $section_animation = array(
        '#type' => 'select',
        '#options' => animate_any_options(),
        '#title' => t('Section Animation'),
        '#default_value' => $value->section_animation,
        '#attributes' => array('class' => array('select_animate')),
      );
      $animation = array(
        '#markup' => 'ANIMATE ANY',
        '#prefix' => '<h1 id="animate" class="" style="font-size: 30px;">',
        '#suffix' => '</h1>',
      );
      $form['animate_fieldset'][$key] = array(
        'section_identity' => &$section_identity,
        'section_animation' => &$section_animation,
        'animation' => &$animation,
      );
      $form['animate_fieldset']['#rows'][$key] = array(
        array('data' => &$section_identity),
        array('data' => &$section_animation),
        array('data' => &$animation),
      );

      unset($section_identity);
      unset($section_animation);
      unset($animation);
    }
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update Settings'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $op = (string) $form_state->getValue('op');
    if ($op == 'Update Settings') {
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
    // update the data for current element
    $parent = $form_state->getvalue('parent_class');
    $aid = $form_state->getvalue('aid');
    $identifiers = json_encode($form_state->getvalue('animate_fieldset'));
    $data = \Drupal::database()->update('animate');
    $data->fields(array(
      'parent' => $parent,
      'identifier' => $identifiers,
    ));
    $data->condition('aid', $aid);
    $valid = $data->execute();
    if ($valid) {
      drupal_set_message(t('Animation settings updated.'));
    }
  }

}
