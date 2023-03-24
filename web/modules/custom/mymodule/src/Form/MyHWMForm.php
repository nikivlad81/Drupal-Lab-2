<?php

namespace Drupal\mymodule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * {@inheritdoc}
 */
class MyHWMForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mymodule_myhwform';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => 'Your name',
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => 'Your e-mail',
    ];

    $form['age'] = [
      '#type' => 'textfield',
      '#title' => 'Your age',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Enter',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $this->t(':name', [':name' => $form_state->getValue("name")]);
    $email = $this->t(':email', [':email' => $form_state->getValue("email")]);
    $age = $this->t(':age', [':age' => $form_state->getValue("age")]);
    $form_state->setRedirectUrl(Url::fromUri("internal:/hw-module/route1/$name/$email/$age"));
  }

}
