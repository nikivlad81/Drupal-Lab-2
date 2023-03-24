<?php

namespace Drupal\ajax_form_submit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements an example form.
 */
class MultistepAjaxForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_multistep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Detect current step.
    $step = $form_state->get('step') ?? 1;

    // Steps titles.
    $step_titles = [
      $this->t('Personal data'),
      $this->t('Parameters'),
      $this->t('Questions'),
    ];

    // Form header.
    $form['title'] = [
      '#type' => 'item',
      '#title' => $this->t('Step :step from 3: :title_form - :title_step', [
        ':step' => $step,
        ':title_form' => $this->t('MultistepForm'),
        ':title_step' => $step_titles[$step - 1],
      ]),
    ];

    // Step 1 fields.
    $form['step1']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $form_state->getValue('name'),
      '#access' => $step === 1,
    ];
    $form['step1']['surname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Surname'),
      '#default_value' => $form_state->getValue('surname'),
      '#access' => $step === 1,
    ];

    // Step 2 fields.
    $form['step2']['age'] = [
      '#type' => 'number',
      '#min' => 18,
      '#max' => 120,
      '#title' => $this->t('Age'),
      '#default_value' => $form_state->getValue('age') ??
      $form_state->get(['data', 'age']) ??
      18,
      '#access' => $step === 2,
    ];
    $form['step2']['gender'] = [
      '#type' => 'select',
      '#options' => [
        'male' => $this->t('Male'),
        'female' => $this->t('Female'),
        'other' => $this->t('Other'),
      ],
      '#title' => $this->t('Gender'),
      '#default_value' => $form_state->getValue('gender') ??
      $form_state->get(['data', 'gender']),
      '#access' => $step === 2,
    ];

    // Step 3 fields.
    $form['step3']['bio'] = [
      '#type' => 'textarea',
      '#rows' => 6,
      '#title' => $this->t('Bio'),
      '#default_value' => $form_state->getValue('bio') ??
      $form_state->get(['data', 'bio']),
      '#access' => $step == 3,
    ];
    $form['step3']['hobby'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hobby'),
      '#options' => [
        'chess' => $this->t('Chess'),
        'football' => $this->t('Football'),
        'politics' => $this->t('Politics'),
        'gardening' => $this->t('Gardening'),
      ],
      '#default_value' => $form_state->getValue('hobby') ??
      $form_state->get(['data', 'hobby']) ?? [],
      '#access' => $step == 3,
    ];

    // Submit buttons.
    $form['actions']['#type'] = 'actions';
    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Prev'),
      '#submit' => ['::prevSubmit'],
      '#limit_validation_errors' => [],
      '#access' => $step > 1,
      '#ajax' => [
    // don't forget :: when calling a class method.
        'callback' => '::myAjaxCallback',
    // This element is updated with this AJAX callback.
        'wrapper' => 'myform-ajax-wrapper',
      ],
    ];
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextSubmit'],
      '#access' => $step < 3,
      '#ajax' => [
    // don't forget :: when calling a class method.
        'callback' => '::myAjaxCallback',
    // This element is updated with this AJAX callback.
        'wrapper' => 'myform-ajax-wrapper',
      ],
    ];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#access' => $step == 3,
    ];

    // Form wrapper for ajax callback.
    $form['#prefix'] = '<div id="myform-ajax-wrapper">';
    $form['#suffix'] = '</div>';

    $form['#tree'] = TRUE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $this->messenger()->addStatus($this->t('Submit'));
    $values = $form_state->getValues();
    $data = $form_state->get('data') ?? [];
    $data = array_merge($data, $values['step3']);
    $name = $data['name'];
    $surname = $data['surname'];
    $age = $data['age'];
    $gender = $data['gender'];
    $bio = $data['bio'];
    $hobby = implode(',', $data['hobby']);
    $form_state->setRedirectUrl(Url::fromUri("internal:/multistep-ajax-form/$name/$surname/$age/$gender/$bio/$hobby"));
  }

  /**
   * {@inheritdoc}
   */
  public function nextSubmit(array &$form, FormStateInterface $form_state) {
    // $this->messenger()->addStatus($this->t('Next'));
    $step = $form_state->get('step') ?? 1;

    // Save step data.
    $values = $form_state->getValues();
    $data = $form_state->get('data') ?? [];
    $form_state->set('data', array_merge($data, $values['step' . $step]));

    // Prepare new step.
    $form_state->set('step', ++$step);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function prevSubmit(array &$form, FormStateInterface $form_state) {
    // $this->messenger()->addStatus($this->t('Prev'));
    $step = $form_state->get('step') ?? 1;

    // Save step data.
    $values = $form_state->getUserInput();
    $data = $form_state->get('data') ?? [];
    $form_state->set('data', array_merge($data, $values['step' . $step]));

    // Restore step data.
    $form_state->setValues($data);

    // Prepare new step.
    $form_state->set('step', --$step);
    $form_state->setRebuild(TRUE);
  }

}
