<?php

namespace Drupal\color_hw\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'color_hw' field type.
 *
 * @FieldType(
 *   id = "color_hw",
 *   label = @Translation("Color hw field"),
 *   description = @Translation("This field from HW task."),
 *   default_widget = "color_hw_default_widget",
 *   default_formatter = "color_hw_default_formatter"
 * )
 */
class ColorHWItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    return [
      'red' => DataDefinition::create('integer')->setLabel(t('Red')),
      'green' => DataDefinition::create('integer')->setLabel(t('Green')),
      'blue' => DataDefinition::create('integer')->setLabel(t('Blue')),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'red' => [
          'type' => 'int',
          'size' => 'small',
          'not null' => TRUE,
        ],
        'green' => [
          'type' => 'int',
          'size' => 'small',
          'not null' => TRUE,
        ],
        'blue' => [
          'type' => 'int',
          'size' => 'small',
          'not null' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isEmpty(): bool {
    $empty = TRUE;
    foreach (['red', 'green', 'blue'] as $column) {
      $empty &= $this->get($column)->getValue() === NULL;
    }

    return $empty;

  }

}
