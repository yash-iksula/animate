<?php
namespace Drupal\animate_any\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form for deleting mymodule data.
 */
class AnimateDeleteForm extends ConfirmFormBase {

  /**
   * The ID of the item to delete.
   *
   * @var strings
   */
  protected $aid;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'animate_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete %id?', array('%aid' => $this->aid));
  }

  /**
   * {@inheritdoc}
   */
    public function getCancelUrl() {
      return new Url('animate_any.animate_delete_form');
  }

  /**
   * {@inheritdoc}
   */
    public function getDescription() {
    return t('Only do this if you are sure!');
  }

  /**
   * {@inheritdoc}
   */
    public function getConfirmText() {
    return t('Delete it!');
  }

  /**
   * {@inheritdoc}
   */
    public function getCancelText() {
    return t('Nevermind');
  }

  /**
   * {@inheritdoc}
   *
   * @param int $id
   *   (optional) The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $aid = NULL) {
    $this->aid = $aid;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    animate_any_delete($this->aid);
  }

}

