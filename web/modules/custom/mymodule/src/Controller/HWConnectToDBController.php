<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class HWConnectToDBController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected Connection $connection;

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
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content() {

    $query = $this->connection->query('SELECT nid FROM {node}');
    $result = $query->fetchCol(0);
    $nids = implode(', ', $result);

    return [
      "#markup" => $this->t('nid: :name', [':name' => $nids]),
    ];
  }

}
