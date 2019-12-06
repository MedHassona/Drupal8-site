<?php

namespace Drupal\media_entity_facebook\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\media_entity_facebook\FacebookMarkup;
use Drupal\media_entity_facebook\Plugin\media\Source\Facebook;

/**
 * Plugin implementation of the 'facebook_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "facebook_embed",
 *   label = @Translation("Facebook embed"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class FacebookEmbedFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $items->getEntity();

    $element = [];
    if (($source = $media->getSource()) && $source instanceof Facebook) {
      foreach ($items as $delta => $item) {
        $element[$delta] = [
          '#markup' => FacebookMarkup::create($source->getMetadata($media, 'html')),
        ];
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getTargetEntityTypeId() === 'media';
  }

}
