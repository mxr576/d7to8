<?php

/**
 * @file
 * Contains \Drupal\d8access\Controller\D8AccessDisplayController.
 */

namespace Drupal\d8access\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

class D8AccessDisplayController extends ControllerBase {

  /**
   * Display a given user's identity.
   *
   * @param \Drupal\Core\Session\AccountInterface|NULL $user
   *   The user to retrieve data on behalf of.
   *
   * @return array
   *   Render array with all the results.
   */
  public function display(AccountInterface $user = NULL) {
    // Build the output (as a render array).
    $output = [
      // This information should not be cached.
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    if ($user) {
      $output['name'] = ['#markup' => $this->t('Name: %name', ['%name' => $user->getDisplayName()])];
      $output['mail'] = ['#markup' => $this->t('Mail: %mail', ['%mail' => $user->getEmail()])];
    };
    return $output;
  }
}
