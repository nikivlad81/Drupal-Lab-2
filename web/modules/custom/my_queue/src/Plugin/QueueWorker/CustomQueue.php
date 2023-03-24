<?php

namespace Drupal\my_queue\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom Queue Worker.
 *
 * @QueueWorker(
 *   id = "my_queue_log",
 *   title = @Translation("My Custom Queue Log"),
 *   cron = {"time" = 60}
 * )
 */
class CustomQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Logging channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('logger.channel.my_queue_log'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function processItem($data) {

    $this->logger->notice(
      '@user (uid: @uid) @op node (type: @type, node-ID: @nid) at @timestamp',
      [
        '@user' => $data['user'],
        '@uid' => $data['uid'],
        '@op' => $data['op'],
        '@type' => $data['type'],
        '@nid' => $data['nid'],
        '@timestamp' => date('H:i d.m.Y', $data['timestamp']),
      ],
      );

  }

}
