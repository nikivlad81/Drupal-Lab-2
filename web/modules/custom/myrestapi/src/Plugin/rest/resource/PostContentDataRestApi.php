<?php

namespace Drupal\myrestapi\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "post_content_rest_resource",
 *   label = @Translation("Content API create"),
 *   uri_paths = {
 *     "create" = "/api/create"
 *   }
 * )
 */
class PostContentDataRestApi extends ResourceBase {

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
      $container->get('current_user')
    );

  }

  /**
   * Responds to POST requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    $role = $this->currentUser->getRoles();

    if ($role[1] != 'administrator' and $role[1] != 'editor') {
      throw new AccessDeniedHttpException('Access denied');
    }

    try {
      $node = $this->entityTypeManager->getStorage('node')->create([
        'type' => $data['type'],
        'title' => $data['title'],
        'body' => $data['body'],
        'langcode' => 'en',
        'uid' => $this->currentUser->id(),
        'status' => 0,
      ]);
      $node->save();

      $response = new ResourceResponse($node);
      $response->addCacheableDependency($node);
      return $response;

    }
    catch (\Exception $e) {
      return new ResourceResponse('Something went wrong during node creation. Check your data.', 400);
    }

  }

}
