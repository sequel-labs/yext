<?php

namespace Drupal\yext\Utility;

use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\Core\Url;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\LinkItemInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * {@inheritdoc}
 *
 * The LinkWidget contains the methods we need to access to transform drupal URIs into displayable strings and back again
 * and to validate urls.
 */
class RedirectUrlWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function getUriAsDisplayableString($uri) {
    return parent::getUriAsDisplayableString($uri);
  }

  /**
   * {@inheritdoc}
   */
  public static function getUserEnteredStringAsUri($uri) {
    return parent::getUserEnteredStringAsUri($uri);
  }

}

