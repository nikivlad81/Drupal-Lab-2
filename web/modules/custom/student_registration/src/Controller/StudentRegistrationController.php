<?php

namespace Drupal\student_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class StudentRegistrationController extends ControllerBase implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected Connection $connection;

  /**
   * {@inheritdoc}
   */
  protected RouteMatchInterface $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, RouteMatchInterface $request) {
    $this->connection = $connection;
    $this->request = $request;
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
  public function content($id) {
    $e_n = 'enrollment_number';
    $query = $this->connection->select('students_data', 'sd');
    $query->fields('sd',
    ['name', $e_n, 'email', 'phone', 'dob', 'sex', 'average_mark']);
    $query->condition('sd.uid', $id);
    $result = $query->execute()->fetchAll();

    $data = $this->t('<b>Name:</b> @name
          <br><b>Enrollment Number:</b> @enrollment_number
          <br><b>Email ID:</b> @email
          <br><b>Contact Number:</b> @phone
          <br><b>DOB:</b> @dob
          <br><b>Gender:</b> @sex
          <br><b>Average mark:</b> @average_mark', [
            '@name' => $result[0]->name,
            '@enrollment_number' => $result[0]->enrollment_number,
            '@email' => $result[0]->email,
            '@phone' => $result[0]->phone,
            '@dob' => $result[0]->dob,
            '@sex' => $result[0]->sex,
            '@average_mark' => $result[0]->average_mark,
          ]);

    return [
      "#markup" => $data,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function index() {

    $header = [
      'name' => $this->t('Name'),
      'email' => $this->t('E-mail'),
      'profile' => $this->t('View profile'),
      'edit' => $this->t('Edit profile'),
      'delete' => $this->t('Delete profile'),
    ];

    $query = $this->connection->select('students_data', 'std')
      ->fields('std', ['uid', 'name', 'email']);
    $result = $query->execute()->fetchAll();

    foreach ($result as $value) {
      $uid = $value->uid;

      $path = $this->request->getRouteObject()->getPath();

      $url = Url::fromUri($this->t('internal:@link/@uid', [
        '@link' => $path,
        '@uid' => $uid,
      ]))
        ->toString();

      $view = $this->t('<a href="@url">View</a>', ['@url' => $url]);
      $edit = $this->t('<a href="@url/edit">Edit</a>', ['@url' => $url]);
      $delete = $this->t('<a href="@url/delete">Delete</a>', ['@url' => $url]);
      $rows[] = [$value->name, $value->email, $view, $edit, $delete];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'contexts' => ['url'],
        'max-age' => 0,
      ],
    ];

  }

}
