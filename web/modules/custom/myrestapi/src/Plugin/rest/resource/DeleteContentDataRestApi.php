<?php

namespace Drupal\myrestapi\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "delete_content_rest_resource",
 *   label = @Translation("Content API delete"),
 *   uri_paths = {
 *     "canonical" = "/api/delete/{id}"
 *   }
 * )
 */
class DeleteContentDataRestApi extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  protected AccountProxyInterface $currentUser;

  /**
   * {@inheritdoc}
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new CreateArticleResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   A Entity Type ManagerI nterface.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   A current user instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
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
      $container->get('logger.factory')->get('myrestapi'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );

  }

  /**
   * Responds to PATCH requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function delete($id) {

    $role = $this->currentUser->getRoles();

    if ($role[1] != 'administrator') {
      throw new AccessDeniedHttpException('Access denied');
    }
    $node = $this->entityTypeManager->getStorage('node')->load($id);
    if ($node == NULL) {
      throw new NotFoundHttpException('Not Found node with that ID');
    }
    try {
      $node = $this->entityTypeManager->getStorage('node')->load($id)->delete();

      $nodes = new ResourceResponse(NULL, 204);
      $nodes->addCacheableDependency($node);
      return $nodes;

    }
    catch (\Exception $e) {
      return new ResourceResponse('Something went wrong during node delete.', 400);
    }

  }

}
