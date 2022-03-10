<?php

namespace Drupal\provider_validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates custom URL suffixes.
 */
class CustomUrlCheckingConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $value = $field->value;

    if (!isset($value)) {
      return NULL;
    }

    $is_valid = TRUE;
    // Only letters, numbers, underscore and dash are allowed.
    $pattern = '/^[A-Za-z0-9_-]*$/';
    if ($value != '' && (!preg_match($pattern, $value))) {
      $this->context->addViolation($constraint->notValidFormat, ['%value' => $value]);
      $is_valid = FALSE;
    }

    $is_unique = TRUE;
    // Check unique value (case-sensitive).
    $entity = $field->getEntity();
    $entity_type = $entity->getEntityTypeId();
    if ($entity_type == 'node') {
      $entity_bundle = $entity->getType();
      $field_name = $field->getFieldDefinition()->getName();
      $bundle_field = 'type';
      $id_field = 'nid';

      if ($this->uniqueValidation($field_name, $value, $entity_type, $bundle_field, $entity_bundle, $id_field)) {
        $this->context->addViolation($constraint->notUnique,
            [
              '%value' => $value,
              '%info' => $this->t('This URL suffix already taken.'),
            ]
          );
        $is_unique = FALSE;
      }
    }

    // Check unique path alias.
    if ($is_valid && $is_unique) {
      $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
      $path_alias = '/' . $value;
      $source_path = '/node/' . $entity->id();

      $alias_objects = $path_alias_storage->loadByProperties([
        'alias' => $path_alias,
      ]);
      foreach ($alias_objects as $alias) {
        if ($alias->path->value != $source_path) {
          $this->context->addViolation($constraint->notUnique,
            [
              '%value' => $value,
              '%info' => $this->t('This URL suffix already taken.'),
            ]
          );
          $is_unique = FALSE;
          break;
        }
      }
    }

  }

  /**
   * Unique validation.
   *
   * @param string $field_name
   *   The name of the field.
   * @param string $value
   *   Value of the field to check for uniqueness.
   * @param string $entity_type
   *   Id of the Entity Type.
   * @param string $bundle_field
   *   Field of the Entity type.
   * @param string $entity_bundle
   *   Bundle of the entity.
   * @param string $id_field
   *   Id field of the entity.
   *
   * @return bool
   *   Whether the entity is unique or not
   */
  private function uniqueValidation($field_name, $value, $entity_type, $bundle_field, $entity_bundle, $id_field) {
    if ($entity_type && $value && $field_name && $bundle_field && $entity_bundle) {
      $query = \Drupal::entityTypeManager()->getStorage($entity_type)->getQuery()
        ->condition($field_name, $value, 'LIKE BINARY')
        ->condition($bundle_field, $entity_bundle)
        ->range(0, 1);
      // Exclude the current entity.
      if (!empty($id = $this->context->getRoot()->getEntity()->id())) {
        $query->condition($id_field, $id, '!=');
      }
      $entities = $query->execute();
      if (!empty($entities)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
