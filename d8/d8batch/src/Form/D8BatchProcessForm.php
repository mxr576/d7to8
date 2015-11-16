<?php

/**
 * @file
 * Contains \Drupal\d8_batch\Form\D8BatchProcessForm.
 */

namespace Drupal\d8batch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class D8BatchProcessForm extends FormBase {

  const CSVFILE_NAME = 'd8batch_demo_feed_sources.csv';

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'd8batch_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!fopen(drupal_get_path('module', 'd8batch') . '/' . self::CSVFILE_NAME, 'r')) {
      $form_state->setError($form, $this->t('@file not found!', ['@file' => self::CSVFILE_NAME]));
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => t('Importing feed sources'),
      'operations' => [],
      'progress_message' => t('Processed @current feed source from @total.'),
      'error_message' => t('An error occurred during processing'),
      'finished' => '_d8batch_batch_finished',
    ];
    // Get the feed sources from the CSV file and add them to the batch
    // operations for later processing.
    if ($res = fopen(drupal_get_path('module', 'd8batch') . '/' . self::CSVFILE_NAME, 'r')) {
      while ($line = fgetcsv($res)) {
        $batch['operations'][] = [
          '_d8batch_batch_operation',
          [array_shift($line)],
        ];
      }
      fclose($res);
    }

    batch_set($batch);
  }
}
