<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class RegistrationForm extends FormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected Connection $connection;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): RegistrationForm|static {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['student_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Name:'),
      '#required' => TRUE,
    ];
    $form['student_rollno'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Enrollment Number:'),
      '#required' => TRUE,
    ];
    $form['student_mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Enter Email ID:'),
      '#required' => TRUE,
    ];
    $form['student_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Enter Contact Number:'),
    ];
    $form['student_dob'] = [
      '#type' => 'date',
      '#title' => $this->t('Enter DOB:'),
      '#required' => TRUE,
    ];
    $form['student_gender'] = [
      '#type' => 'select',
      '#title' => ('Select Gender:'),
      '#options' => [
        'Male' => $this->t('Male'),
        'Female' => $this->t('Female'),
        'Other' => $this->t('Other'),
      ],
    ];
    $form['average_mark'] = [
      '#type' => 'number',
      '#step' => 0.01,
      '#required' => TRUE,
      '#min' => 1,
      '#max' => 5,
      '#title' => $this->t('Enter Average Mark:'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('student_rollno')) < 8) {
      $form_state->setErrorByName('student_rollno', $this->t('Please enter a valid Enrollment Number'));
    }
    if (strlen($form_state->getValue('student_phone')) < 10) {
      $form_state->setErrorByName('student_phone', $this->t('Please enter a valid Contact Number'));
    }
    $name = $form_state->getValue("student_name");
    $query = $this->connection->select('students_data', 'sd');
    $query->fields('sd', ['uid', 'name']);
    $query->condition('sd.name', $name);
    $result = $query->execute()->fetchAll();

    if ($result) {
      $form_state->setErrorByName('student_name', $this->t('A student with this name already exists.'));
    }
  }

  /**
   * Submit button.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue("student_name");
    $this->connection->insert('students_data')
      ->fields([
        'name' => $name,
        'enrollment_number' => $form_state->getValue("student_rollno"),
        'email' => $form_state->getValue("student_mail"),
        'phone' => $form_state->getValue("student_phone"),
        'dob' => $form_state->getValue("student_dob"),
        'sex' => $form_state->getValue("student_gender"),
        'average_mark' => $form_state->getValue("average_mark"),
      ])
      ->execute();

    $query = $this->connection->select('students_data', 'sd');
    $query->fields('sd', ['uid', 'name']);
    $query->condition('sd.name', $name);
    $result = $query->execute()->fetchAll();
    $id = $result[0]->uid;
    $form_state->setRedirectUrl(Url::fromUri("internal:/student-registration/$id"));
  }

}
