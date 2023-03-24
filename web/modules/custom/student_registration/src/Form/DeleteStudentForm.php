<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class DeleteStudentForm extends ConfirmFormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  protected Connection $connection;

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;
  /**
   * {@inheritdoc}
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, RouteMatchInterface $routeMatch) {
    $this->connection = $connection;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {

    $this->id = $id;
    return parent::buildForm($form, $form_state);

  }

  /**
   * Submit button.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid = $this->routeMatch->getParameter('name');
    $this->connection->delete('students_data')
      ->condition('uid', $uid)
      ->execute();
    $form_state->setRedirectUrl(Url::fromUri("internal:/student-registration"));

  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $uid = $this->routeMatch->getParameter('name');
    $query = $this->connection->select('students_data', 'sd');
    $query->fields('sd', ['name']);
    $query->condition('sd.uid', $uid);
    $result = $query->execute()->fetchField();
    return $this->t('Do you want to delete %id?', ['%id' => $result]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('student_registration.content.terms');
  }

}
