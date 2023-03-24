<?php

namespace Drupal\cachebin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom block for Cache bin.
 *
 * @Block(
 *   id = "custom_block_for_cache_bin",
 *   admin_label = @Translation("Custom Block For Cache Bin"),
 * )
 */
class CustomBlockForCacheBin extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $backend;
  /**
   * {@inheritdoc}
   */
  protected AccountProxyInterface $currentUser;
  private const CACHE_BIN = 'cache_bin_asus';
  private const CACHE_ID = 'bin_asus';

  /**
   * Constructs a new HomeController object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheBackendInterface $backend, AccountProxyInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->backend = $backend;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): CustomBlockForCacheBin|static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.' . self::CACHE_BIN),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setCache($name, $uid, $cache_number, $max_age) {
    $data = $this->t('Hi, @name
                        <br>Your cache number: @cache_number
                        <br>max-age = @max_age', [
                          '@name' => $name,
                          '@cache_number' => $cache_number,
                          '@max_age' => $max_age,
                        ]);
    $tags = ['user:' . $uid];
    $this->backend->set(self::CACHE_ID, $data, time() + $max_age, $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $name = $this->currentUser->getAccountName();
    $uid = $this->currentUser->id();
    $cache_number = rand(0, 100);
    $max_age = 60;
    $data = $this->t('Hi, @name
     <br>Your cache number: @cache_number
     <br>max-age = @max_age', [
       '@name' => $name,
       '@cache_number' => $cache_number,
       '@max_age' => $max_age,
     ]);

    return [
      '#markup' => $data,
      '#cache' => [
        'contexts' => [
          'user',
        ],
        'tags' => [
          'user:' . $uid,
        ],
        'max-age' => $max_age,
        'bin' => 'cache_bin_asus',
      ],
    ];
  }

}
