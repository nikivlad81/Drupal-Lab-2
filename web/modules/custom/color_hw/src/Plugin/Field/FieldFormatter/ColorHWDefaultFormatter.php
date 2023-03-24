<?php

namespace Drupal\color_hw\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'color_hw_default_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "color_hw_default_formatter",
 *   label = @Translation("ColorHW default Formatter"),
 *   field_types = {
 *     "color_hw"
 *   }
 * )
 */
class ColorHWDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'width' => '250',
      'height' => '250',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#field_suffix' => 'px.',
      '#default_value' => $this->getSetting('width'),
      '#min' => 1,
    ];

    $elements['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#field_suffix' => 'px.',
      '#default_value' => $this->getSetting('height'),
      '#min' => 1,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    $summary[] = $this->t('Width @width px.', ['@width' => $settings['width']]);
    $summary[] = $this->t('Height @height px.', ['@height' => $settings['height']]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = [];
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => new FormattableMarkup(
          '<div style="width: @width; height: @height; background-color: rgb(@red, @green, @blue);"></div>',
          [
            '@width' => $settings['width'] . 'px',
            '@height' => $settings['height'] . 'px',
            '@red' => $item->red,
            '@green' => $item->green,
            '@blue' => $item->blue,
          ]
        ),
      ];
    }

    return $element;
  }

}
