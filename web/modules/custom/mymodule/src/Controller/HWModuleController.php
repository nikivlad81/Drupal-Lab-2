<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * {@inheritdoc}
 */
class HWModuleController {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function content($name, $email, $age) {

    return [
      "#markup" => $this->t('<b>Name:</b> @name<br><b>Email:</b> @email<br><b>Age:</b> @age', [
        '@name' => $name,
        '@email' => $email,
        '@age' => $age,
      ]),
    ];
  }

}
