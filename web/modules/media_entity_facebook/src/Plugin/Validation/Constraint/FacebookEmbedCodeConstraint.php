<?php

namespace Drupal\media_entity_facebook\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check if a value is a valid Facebook embed code or post URL.
 *
 * @constraint(
 *   id = "FacebookEmbedCode",
 *   label = @Translation("Facebook embed code", context = "Validation"),
 *   type = { "link", "string", "string_long" }
 * )
 */
class FacebookEmbedCodeConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Not valid Facebook post URL/embed code.';

}
