<?php

namespace Drupal\student;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the student entity type.
 */
class StudentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total students: @total', ['@total' => $total]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Name');
    $header['surname'] = $this->t('Surname');
    $header['email'] = $this->t('E-mail');
    $header['phone'] = $this->t('Phone');
    $header['age'] = $this->t('Age');
    $header['gender'] = $this->t('Gender');
    $header['group_id'] = $this->t('Group Id');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\student\StudentInterface $entity */
    $row['id'] = $entity->id();
    $row['name'] = $entity->toLink();
    $row['surname'] = $entity->surname->value;
    $row['email'] = $entity->email->value;
    $row['phone'] = $entity->phone->value;
    $row['age'] = $entity->age->value;
    $row['gender'] = $entity->gender->value;
    $row['group_id'] = $entity->group_id->value;
    return $row + parent::buildRow($entity);
  }

}
