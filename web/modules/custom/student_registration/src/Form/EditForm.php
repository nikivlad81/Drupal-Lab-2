<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class EditForm extends FormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected Connection $connection;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $uid = $this->routeMatch->getParameter('name');
    $e_n = 'enrollment_number';
    $query = $this->connection->select('students_data', 'sd');
    $query->fields('sd',
      ['name', $e_n, 'email', 'phone', 'dob', 'sex', 'average_mark']);
    $query->condition('sd.uid', $uid);
    $result = $query->execute()->fetchAll();

    $form['student_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Name:'),
      '#default_value' => $result[0]->name,
    ];
    $form['student_rollno'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Enrollment Number:'),
      '#default_value' => $result[0]->enrollment_number,
    ];
    $form['student_mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Enter Email ID:'),
      '#default_value' => $result[0]->email,
    ];
    $form['student_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Enter Contact Number:'),
      '#default_value' => $result[0]->phone,
    ];
    $form['student_dob'] = [
      '#type' => 'date',
      '#title' => $this->t('Enter DOB:'),
      '#default_value' => $result[0]->dob,
    ];
    $form['student_gender'] = [
      '#type' => 'select',
      '#title' => ('Select Gender:'),
      '#options' => [
        'Male' => $this->t('Male'),
        'Female' => $this->t('Female'),
        'Other' => $this->t('Other'),
      ],
      '#default_value' => $result[0]->sex,
    ];
    $form['uid'] = [
      '#type' => 'hidden',
      '#value' => $uid,
    ];
    $form['average_mark'] = [
      '#type' => 'number',
      '#step' => 0.01,
      '#min' => 1,
      '#max' => 5,
      '#title' => $this->t('Enter Average Mark:'),
      '#default_value' => $result[0]->average_mark,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Edit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('student_rollno')) < 6) {
      $form_state->setErrorByName('student_rollno', $this->t('Please enter a valid Enrollment Number'));
    }
    if (strlen($form_state->getValue('student_phone')) < 10) {
      $form_state->setErrorByName('student_phone', $this->t('Please enter a valid Contact Number'));
    }

  }

  /**
   * Submit form.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid = $form_state->getValue("uid");
    $this->connection->update('students_data')
      ->fields([
        'name' => $form_state->getValue("student_name"),
        'enrollment_number' => $form_state->getValue("student_rollno"),
        'email' => $form_state->getValue("student_mail"),
        'phone' => $form_state->getValue("student_phone"),
        'dob' => $form_state->getValue("student_dob"),
        'sex' => $form_state->getValue("student_gender"),
        'average_mark' => $form_state->getValue("average_mark"),
      ])
      ->condition('uid', $uid)
      ->execute();

    $form_state->setRedirectUrl(Url::fromUri("internal:/student-registration/$uid"));

  }

}
