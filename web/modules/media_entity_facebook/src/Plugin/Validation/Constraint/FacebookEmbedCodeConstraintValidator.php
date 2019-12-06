<?php

namespace Drupal\media_entity_facebook\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\media_entity_facebook\Plugin\media\Source\Facebook;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the FacebookEmbedCode constraint.
 */
class FacebookEmbedCodeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (is_string($value)) {
      $data = $value;
    }
    elseif ($value instanceof FieldItemInterface) {
      $class = get_class($value);
      $property = $class::mainPropertyName();
      if ($property) {
        $data = $value->$property;
      }
    }
    else {
      $data = '';
    }
    if ($data) {
      $post_url = Facebook::parseFacebookEmbedField($value);
      if ($post_url === FALSE) {
        $this->context->addViolation($constraint->message);
      }
    }
  }

}
