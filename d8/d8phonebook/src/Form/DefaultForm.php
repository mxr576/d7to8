<?php

/**
 * @file
 * Contains \Drupal\d8phonebook\Form\DefaultForm.
 */

namespace Drupal\d8phonebook\Form;

use Drupal\Core\Database\Query\Merge;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Add/edit a phonebook entry.
 */
class DefaultForm extends FormBase {

  /**
   * \Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'd8phonebook_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RouteMatch $routeMatch = NULL) {
    $pbid = NULL;
    if ($routeMatch->getRouteName() == 'd8phonebook.edit') {
      // If we're on an edit URL, try to retrieve the phonebook entry by its ID.
      $pbid = $routeMatch->getParameter('phonebook');
      $query = $this->connection->select('phonebook', 'p')
        ->fields('p');
      $query->condition('pbid', $pbid);
      $entry = $query->execute()
        ->fetchObject();
      if (!$entry) {
        // Return 404 if the entry is not found.
        throw new NotFoundHttpException();
      }
    }
    // Push the phonebook entry ID to the submit handler.
    $form['pbid'] = [
      '#type' => 'value',
      '#value' => $pbid,
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 64,
      '#default_value' => $pbid ? $entry->name : '',
    ];
    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#maxlength' => 64,
      '#default_value' => $pbid ? $entry->phone : '',
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'save' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    if (Unicode::strlen($name) > 64) {
      $form_state->setErrorByName('name', $this->t('The name cannot be longer than 64 characters.'));
    }
    $phone = $form_state->getValue('phone');
    if (Unicode::strlen($phone) > 64) {
      $form_state->setErrorByName('phone', $this->t('The phone cannot be longer than 64 characters.'));
    }
    else {
      // Validate phone uniqueness amongst all (other) entries.
      $query = $this->connection->select('phonebook', 'p')
        ->fields('p', ['pbid']);
      $query->condition('phone', $phone);
      if ($pbid = $form_state->getValue('pbid')) {
        $query->condition('pbid', $pbid, '<>');
      }
      $result = $query->execute()->fetchObject();
      if ($result) {
        $form_state->setErrorByName('phone', $this->t('The phone must be unique.'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $name = $form_state->getValue('name');
      $result = $this->connection->merge('phonebook')
        ->key('pbid', $form_state->getValue('pbid'))
        ->insertFields([
          'created' => REQUEST_TIME,
        ])
        ->fields([
          'changed' => REQUEST_TIME,
          'name' => $name,
          'phone' => $form_state->getValue('phone'),
        ])->execute();

      if ($result === Merge::STATUS_INSERT) {
        drupal_set_message($this->t('New entry added for %name.', ['%name' => $name]));
      }
      elseif ($result === Merge::STATUS_UPDATE) {
        drupal_set_message($this->t('Updated entry for %name.', ['%name' => $name]));
      }
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('Something went wrong, please try again later!'), 'error');
    }
    $form_state->setRedirect('d8phonebook.index');
  }
}
