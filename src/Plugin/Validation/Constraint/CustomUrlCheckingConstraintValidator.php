<?php

namespace Drupal\provider_validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates custom URL suffixes.
 */
class CustomUrlCheckingConstraintValidator extends ConstraintValidator {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $value = $field->value;

    if (!isset($value)) {
      return NULL;
    }

    // Only letters, numbers, underscore and dash are allowed.
    $pattern = '/^[A-Za-z0-9_-]*$/';
    if ($value != '' && (!preg_match($pattern, $value))) {
      $this->context->addViolation($constraint->notValidFormat,
        ['%value' => $value]);
    }

    // Check unique value (case-sensitive).
    if (strpos($value, '/') === 0) {
      $checked_url = \Drupal::pathValidator()->getUrlIfValid($value);
    }
    else {
      $checked_url = \Drupal::pathValidator()->getUrlIfValid('/' . $value);
    }

    $entity = $field->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $id = $entity->id();

    $entity_url = \Drupal::pathValidator()->getUrlIfValid('/' . $entity_type . '/' . $id);
    if ($checked_url && $checked_url != $entity_url) {
      $this->context->addViolation($constraint->notUnique,
        [
          '%value' => $value,
          '%info' => $this->t('This URL suffix already taken'),
        ]
      );
    }
  }

}
