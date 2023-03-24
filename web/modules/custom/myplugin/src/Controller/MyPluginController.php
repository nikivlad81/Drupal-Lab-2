<?php

namespace Drupal\myplugin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * {@inheritdoc}
 */
class MyPluginController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function describeLatestNode(): array {
    return [
      '#murkup' => '',
    ];

  }

}
