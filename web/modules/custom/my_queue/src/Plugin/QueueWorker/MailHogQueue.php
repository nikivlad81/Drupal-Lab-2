<?php

namespace Drupal\my_queue\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\my_queue\Mail\MailHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom Queue Worker.
 *
 * @QueueWorker(
 *   id = "my_queue_mailhog",
 *   title = @Translation("My Custom Queue Mail Hog"),
 *   cron = {"time" = 60}
 * )
 */
class MailHogQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  /**
   * {@inheritdoc}
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected MailManager $mailManager;

  /**
   * The mail handler.
   *
   * @var \Drupal\my_queue\Mail\MailHandler
   */
  protected $mailHandler;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, MailManager $mailManager, MailHandler $mail_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->mailManager = $mailManager;
    $this->mailHandler = $mail_handler;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.mail'),
      $container->get('my_queue.mail_handler')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function processItem($data) {

    $user_ids = $this->entityTypeManager->getStorage('user')->getQuery()->execute();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($user_ids);
    $node = $this->entityTypeManager->getStorage('node')->load($data['nid']);
    if (empty($node)) {
      return;
    }
    $path = $node->toLink()->toString();

    foreach ($users as $user) {

      if ($user->login->value == 0) {
        continue;
      }

      $name = $user->name->value;
      $langcode = $user->preferred_langcode->value;
      $to = $user->mail->value;

      $subject = new TranslatableMarkup('Create a new post "@title" in content type "@type"', [
        '@title' => $data['title'],
        '@type' => $data['type'],
      ]);

      $body = [
        'head' => [
          '#type' => 'html_tag',
          '#tag' => 'b',
          '#value' => new TranslatableMarkup('Hi, @name', [
            '@name' => $name,
          ]),
        ],
        'body' => [
          '#suffix' => '<br>',
          '#markup' => new TranslatableMarkup('There is a new entry on our website. You can watch it at the link below'),
        ],
        'link' => [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#prefix' => '<br>',
          '#value' => $path,
        ],
      ];

      $params = [
        'id' => 'send_mass',
        'langcode' => $langcode,
      ];
      $this->mailHandler->sendMail($to, $subject, $body, $params);
    }

  }

}
