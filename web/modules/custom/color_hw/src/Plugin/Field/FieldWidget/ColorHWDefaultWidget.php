<?php

namespace Drupal\color_hw\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'color_hw_default_widget' widget.
 *
 * @FieldWidget(
 *   id = "color_hw_default_widget",
 *   label = @Translation("ColorHW default Widget"),
 *   field_types = {
 *     "color_hw"
 *   }
 * )
 */
class ColorHWDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'size' => 20,
      'maxlength' => 3,
      'placeholder' => 'Enter color numbers',
      'min' => 0,
      'max' => 255,
      'required' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element['placeholder'] = [
      '#type' => 'textfield',
      '#title' => 'Placeholder',
      '#default_value' => $this->getSetting('placeholder'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

    $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    $summary[] = $this->t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    $summary[] = $this->t('Textfield length: @maxlength', ['@maxlength' => $this->getSetting('maxlength')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {

    $element['red'] = [
      '#title' => $this->t('Red'),
      '#type' => 'textfield',
      '#size' => $this->getSetting('size'),
      '#maxlength' => $this->getSetting('maxlength'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#min' => $this->getSetting('min'),
      '#max' => $this->getSetting('max'),
      '#required' => $this->getSetting('required'),
      '#default_value' => $items[$delta]->red ?? NULL,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];
    $element['green'] = [
      '#title' => $this->t('Green'),
      '#type' => 'textfield',
      '#size' => $this->getSetting('size'),
      '#maxlength' => $this->getSetting('maxlength'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#min' => $this->getSetting('min'),
      '#max' => $this->getSetting('max'),
      '#required' => $this->getSetting('required'),
      '#default_value' => $items[$delta]->green ?? NULL,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];
    $element['blue'] = [
      '#title' => $this->t('Blue'),
      '#type' => 'textfield',
      '#size' => $this->getSetting('size'),
      '#maxlength' => $this->getSetting('maxlength'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#min' => $this->getSetting('min'),
      '#max' => $this->getSetting('max'),
      '#required' => $this->getSetting('required'),
      '#default_value' => $items[$delta]->blue ?? NULL,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];
    return $element;
  }

  /**
   * Validate the color text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];

    if ($value > 255 or $value < 0) {
      $form_state->setError($element, t('The color number in field @color must be between 0 and 255', ['@color' => $element['#title']]));

    }

  }

}
