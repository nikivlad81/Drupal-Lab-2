<?php

namespace Drupal\my_queue\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure My Queue Batch settings for this site.
 */
class BatchForm extends FormBase {

  /**
   * An array with available node types.
   *
   * @var array|mixed
   */
  protected $nodeBundles;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * Node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * BatchForm constructor.
   */
  public function __construct(EntityTypeBundleInfo $entity_type_bundle_info, NodeStorageInterface $node_storage) {
    $this->nodeBundles = $entity_type_bundle_info->getBundleInfo('node');
    // Move bundle name from 'label' key to value.
    array_walk($this->nodeBundles, function (&$a) {
        $a = $a['label'];
    });

    $this->nodeStorage = $node_storage;
    $this->batchBuilder = new BatchBuilder();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('entity_type.bundle.info'),
          $container->get('entity_type.manager')->getStorage('node')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_queue_batch';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = [
      '#markup' => $this->t('This form adds the publication date to the title of each node in the selected content type.'),
    ];

    $form['node_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $this->nodeBundles,
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run batch'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nodes = $this->getNodes($form_state->getValue(['node_type']));

    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    $this->batchBuilder->setFile(drupal_get_path('module', 'my_queue') . '/src/Form/BatchForm.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [$nodes]);
    $this->batchBuilder->setFinishCallback([$this, 'finished']);

    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
  public function processItems($items, array &$context) {

    $limit = 50;

    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }

    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $items;
    }

    $counter = 0;
    if (!empty($context['sandbox']['items'])) {

      if ($context['sandbox']['progress'] != 0) {
        array_splice($context['sandbox']['items'], 0, $limit);
      }

      foreach ($context['sandbox']['items'] as $item) {
        if ($counter != $limit) {
          $this->processItem($item);

          $counter++;
          $context['sandbox']['progress']++;

          $context['message'] = $this->t('Now processing node :progress of :count', [
            ':progress' => $context['sandbox']['progress'],
            ':count' => $context['sandbox']['max'],
          ]);

          $context['results']['processed'] = $context['sandbox']['progress'];
        }
      }
    }

    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Process single item.
   *
   * @param int|string $nid
   *   An id of Node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItem($nid) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->nodeStorage->load($nid);
    $p_date = date('H:i d.m.Y', $node->getCreatedTime());
    $old_title = $node->getTitle();
    $node->setTitle($old_title . ' (' . $p_date . ')')->save();
  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    $message = $this->t('Number of nodes affected by batch: @count', [
      '@count' => $results['processed'],
    ]);

    $this->messenger()
      ->addStatus($message);
  }

  /**
   * Load all nids for specific type.
   *
   * @return array
   *   An array with nids.
   */
  public function getNodes($type) {
    return $this->nodeStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', $type)
      ->execute();
  }

}
