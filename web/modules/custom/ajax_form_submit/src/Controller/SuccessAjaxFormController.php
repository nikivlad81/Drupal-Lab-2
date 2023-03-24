<?php

namespace Drupal\ajax_form_submit\Controller;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * {@inheritdoc}
 */
class SuccessAjaxFormController {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function content($name, $surname, $age, $gender, $bio, $hobby) {

    $data = [
      'name' => $name,
      'surname' => $surname,
      'age' => $age,
      'gender' => $gender,
      'bio' => $bio,
      'hobby' => $hobby,
    ];

    foreach ($data as $key => $value) {
      $this->messenger()->addStatus($this->t(':key => :value', [
        ':key' => $key,
        ':value' => is_array($value) ? implode(', ', array_filter($value)) : $value,
      ]));
    }
    return [
      "#markup" => '',
    ];
  }

}
