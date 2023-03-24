<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class LatestNodesController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, $service) {
    $this->entityTypeManager = $entityTypeManager;
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('mymodule.latestNodesService'),

    );
  }

  /**
   * {@inheritdoc}
   */
  public function service() {
    $nodes = $this->service->doContent();

    return [
      '#theme' => 'latest_nodes_service',
      '#nodes' => $nodes,
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function latestNodesController() {

    $node_ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'blog')
      ->sort('nid', 'DESC')
      ->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);
    $k = 0;
    $titles = [];
    foreach ($nodes as $node) {
      if ($k > 5) {
        break;
      }
      $iid = $node->field_image_hw->getString();
      if (strlen($iid) < 1) {
        continue;
      }
      $file = $this->entityTypeManager
        ->getStorage('file')
        ->load($iid);

      $path = substr($file->uri->value, 9);

      if (mb_strlen($node->getTitle()) > 17) {
        $title = mb_substr($node->getTitle(), 0, 13) . '...';
      }
      else {
        $title = $node->getTitle();
      }

      $titles[] = [
        'nid' => $node->id(),
        'title' => $title,
        'path' => '/sites/default/files/styles/large/public/' . $path,
      ];
      $k++;
    }

    return [
      '#theme' => 'latest_nodes_controller',
      '#nodes' => $titles,
    ];
  }

}
