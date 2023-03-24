<?php

namespace Drupal\mymodule;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class LatestNodesService implements ContainerInjectionInterface {

  use StringTranslationTrait;
  /**
   * {@inheritdoc}
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  private function findIds(): array {

    $node_ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'blog')
      ->sort('nid', 'DESC')
      ->execute();
    return $node_ids;
  }

  /**
   * {@inheritdoc}
   */
  private function doLoadNodes(): array {
    $node_ids = $this->findIds();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function doContent() {
    $nodes = $this->doLoadNodes();
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

    return $titles;

  }

}
