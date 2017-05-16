<?php

/**
 * @file
 * Contains \Drupal\animate_any\Controller\AnimateListController.
 */

namespace Drupal\animate_any\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class AnimateListController extends ControllerBase {

  public function animate_list() {
    $header = array();
    $header[] = array('data' => t('ID'));
    $header[] = array('data' => t('Parent element'));
    $header[] = array('data' => t('Identifiers'));
    $header[] = array('data' => t('Operation'));
// Fetch Animate Data.
    $fetch = \Drupal::database()->select("animate", "a");
    $fetch->fields('a');
    $fetch->orderBy('aid', 'DESC');
//    $fetch = $fetch->extend('TableSort')->orderByHeader($header);
//    $fetch = $fetch->extend('PagerDefault')->limit(5);
    $fetch_results = $fetch->execute()->fetchAll();
    foreach ($fetch_results as $items) {
      $mini_header = array();
      $mini_header[] = array('data' => t('Section'));
      $mini_header[] = array('data' => t('Animation'));
      $mini_rows = array();
      $data = \json_decode($items->identifier);
      foreach ($data as $value) {
        $mini_rows[] = array($value->section_identity, $value->section_animation);
      }
      $mini_output = array();
      $mini_output['mini_list'] = [
        '#theme' => 'table',
        '#header' => $mini_header,
        '#rows' => $mini_rows,
        ];

      $identifiers = drupal_render($mini_output);

      $edit = \Drupal::l(t('edit'), Url::fromUri('internal:/admin/config/animate_any/edit/' . $items->aid));
      $delete = \Drupal::l(t('delete'), Url::fromUri('internal:/admin/config/animate_any/delete/' . $items->aid));


      $rows[] = array(
        $items->aid, $items->parent, $identifiers, $edit, $delete,
      );
    }
    $add = \Drupal::l(t('Add Animation'), Url::fromUri('internal:/admin/config/animate_any/add', ['attributes' => ['class' => ['button']]]));
    $add_link = '<ul class="action-links"><li>' . $add . '</li></ul>';
    if (count($fetch_results) > 0) {
      $output['animate_list'] = array(
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#prefix' => $add_link,
      );
      $output['pager'] = array(
        '#type' => 'pager'
      );
    }
    else {
      $output['animate_list'] = array(
        '#prefix' => $add_link,
        '#suffix' => 'No record found',
      );
    }
    return $output;
  }

}
