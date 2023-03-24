<?php

namespace Drupal\myrestapi\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides REST API for Content Based on URL.
 *
 * @RestResource(
 *   id = "get_content_rest_resource",
 *   label = @Translation("Content API"),
 *   uri_paths = {
 *     "canonical" = "/api/content"
 *   }
 * )
 */
class GetContentDataRestApi extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected Request $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entityTypeManager, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entityTypeManager;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returning rest resource.
   */
  public function get() {

    if ($this->request->query->has('type')) {

      $type = $this->request->query->get('type');

      if (!empty($type)) {

        if ($this->request->query->has('limit')) {
          $limit = $this->request->query->get('limit');
        }
        else {
          $limit = 5;
        }

        $node_ids = $this->entityTypeManager->getStorage('node')->getQuery()
          ->condition('type', $type)
          ->sort('nid', 'DESC')
          ->range(0, $limit)
          ->execute();
        $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);

        foreach ($nodes as $node) {

          if ($node->status->value == FALSE) {
            continue;
          }

          $response[$node->nid->value] = [
            'title' => $node->title->value,
            'body' => $node->body->value,
            'created' => date('H:i d.m.Y', $node->created->value),
            'uid' => $node->uid->target_id,
          ];

        }

        $nodes = new ResourceResponse($response);
        $nodes->addCacheableDependency($response);
        return $nodes;

      }
    }
    return new ResourceResponse('The parameter is entered incorrectly or there is no data to display.', 400);
  }

}
