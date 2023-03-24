<?php

namespace Drupal\myplugin;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * {@inheritdoc}
 */
class DescribeLatestNodeService {

  use StringTranslationTrait;
  /**
   * {@inheritdoc}
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected ConfigFactory $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactory $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function findIds(): array {

    $config = $this->configFactory

      ->get('myplugin.settings');

    return $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', $config->get('type'))
      ->sort('nid', 'DESC')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function doLoadNodes(): array {
    $node_ids = $this->findIds();
    return $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function describeColors() {
    $nodes = $this->doLoadNodes();
    $titles = [];

    foreach ($nodes as $node) {
      if (strlen($node->getTitle()) > 20) {
        $title = mb_substr($node->getTitle(), 0, 16) . '...';
      }
      else {
        $title = $node->getTitle();
      }
      $color = $node->field_c->getString();
      $numbers_color = explode(",", $color);
      $background = $this->t('background-color: rgb(@red, @green, @blue)', [
        '@red' => $numbers_color[0],
        '@green' => $numbers_color[1],
        '@blue' => $numbers_color[2],
      ]);

      $view = $this->t('<a href="/node/@node"><span>@title</span></a>', [
        '@node' => $node->id(),
        '@title' => $title,
      ]);

      $titles[] = [
        '#markup' => $view,
        '#type' => 'container',
        '#attributes' => [
          'class' => 'color_latest_node',
          'style' => $background,
        ],
      ];
    }
    return $titles;
  }

}
