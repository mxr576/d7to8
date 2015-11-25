<?php

/**
 * @file
 * Contains \Drupal\d8phonebook\Controller\DefaultController.
 */

namespace Drupal\d8phonebook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Url;
use Drupal\Core\Access\CsrfTokenGenerator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class DefaultController.
 *
 * @package Drupal\d8phonebook\Controller
 */
class DefaultController extends ControllerBase {

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var Drupal\Core\Datetime\DateFormatter
   */
  protected $date_formatter;

  /**
   * Drupal\Core\Access\CsrfTokenGenerator definition.
   *
   * @var Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrf_token_generator;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, DateFormatter $date_formatter, CsrfTokenGenerator $csrf_token_generator) {
    $this->connection = $connection;
    $this->date_formatter = $date_formatter;
    $this->csrf_token_generator = $csrf_token_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('csrf_token')
    );
  }

  /**
   * Index.
   *
   * @return array
   *   Render array with all the entries.
   */
  public function index() {
    $output = [
      // This information should not be cached.
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    // This is going to be reused at two places: once for the TableSortExtender,
    // and once for the table header itself.
    $header = [
      ['data' => $this->t('Created'), 'field' => 'p.created'],
      ['data' => $this->t('Changed'), 'field' => 'p.changed'],
      ['data' => $this->t('Name'), 'field' => 'p.name'],
      ['data' => $this->t('Phone'), 'field' => 'p.phone'],
      ['data' => $this->t('Operations'), 'colspan' => '2'],
    ];
    $query = $this->connection->select('phonebook', 'p')
      // Add support for table sorts.
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      // Add support for a pager.
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('p');
    $result = $query
      // The results should be sorted based on what column the user clicks on.
      ->orderByHeader($header)
      // There are 25 items per page.
      ->limit(25)
      ->execute();
    $output['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No entries found.'),
    ];
    foreach ($result as $row) {
      $output['table'][] = [
        ['data' => ['#markup' => $this->date_formatter->format($row->created)]],
        ['data' => ['#markup' => $this->date_formatter->formatTimeDiffSince($row->changed)]],
        ['data' => ['#markup' => $row->name]],
        ['data' => ['#markup' => $row->phone]],
        ['data' => ['#markup' => $this->l($this->t('edit'), new Url('d8phonebook.edit', ['phonebook' => $row->pbid]))]],
        ['data' => ['#markup' => $this->l($this->t('delete'), new Url('d8phonebook.delete', ['phonebook' => $row->pbid], ['query' => ['token' => $this->csrf_token_generator->get('phonebook/' . $row->pbid . '/delete')]]))]],
      ];
    }
    $output['pager'] = array(
      '#type' => 'pager',
    );
    return $output;
  }

  /**
   * Delete a phonebook entry.
   *
   * @param \Drupal\Core\Routing\RouteMatch $routeMatch
   */
  public function delete(RouteMatch $routeMatch) {
    // CSRF is already checked by the routing system, so we're only making sure
    // that an existing item is being deleted.
    $pbid = $routeMatch->getParameter('phonebook');
    $query = $this->connection->select('phonebook', 'p')
      ->fields('p', ['name']);
    $query->condition('pbid', $pbid);
    $entry = $query->execute()
      ->fetchObject();
    if (!$entry) {
      // Return a 404 if the entry is not found.
      throw new NotFoundHttpException();
    }
    $this->connection->delete('phonebook')
      ->condition('pbid', $pbid)
      ->execute();
    drupal_set_message($this->t('Deleted entry for %name.', ['%name' => $entry->name]));
    return new RedirectResponse('/phonebook');
  }

}
