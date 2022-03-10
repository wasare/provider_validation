<?php

namespace Drupal\provider_validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Custom URL constraint.
 *
 * Validation component for custom urls suffixes.
 *
 * @Constraint(
 *   id = "CustomUrlChecking",
 *   label = @Translation("Custom URL constraint", context = "Validation"),
 * )
 */
class CustomUrlCheckingConstraint extends Constraint {

  /**
   * {@inheritdoc}
   */
  public $notValidFormat = '%value is not valid. Only letters, numbers, underscore and dash are allowed.';

  /**
   * {@inheritdoc}
   */
  public $notUnique = '%value is not unique. %info.';

}
