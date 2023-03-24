<?php

namespace Drupal\myplugin\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a form that configures forms module settings.
 */
class EditConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected $database;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database) {
    parent::__construct($config_factory);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'myplugin.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $query = $this->database->select('node', 'n')
      ->fields('n', ['type'])
      ->groupBy("n.type")
      ->execute();
    $types = $query->fetchAll();

    foreach ($types as $type) {
      $options[$type->type] = $type->type;
    }

    $config = $this->config('myplugin.settings');
    $form['default_count'] = [
      '#type' => 'number',
      '#step' => 1,
      '#min' => 1,
      '#title' => $this->t('Сount of nodes'),
      '#default_value' => $config->get('count'),
    ];
    $form['default_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $options,
      '#default_value' => $config->get('type'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('myplugin.settings')
      ->set('count', $values['default_count'])
      ->set('type', $values['default_type'])
      ->save();
    $form_state->setRedirectUrl(Url::fromUri("internal:/custom-plugin"));
  }

}
