<?php

namespace Drupal\bootstrap_datepicker\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\bootstrap_datepicker\Plugin\Field\FieldWidget\BootstrapDatepickerBase;

/**
 * Provides a BootstrapDate form element.
 *
 * @FormElement("bootstrap_datepicker")
 */
class BootstrapDate extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#maxlength' => 512,
      '#process' => [[$class, 'processBootstrapDate']],
      '#pre_render' => [[$class, 'preRenderBootstrapDate']],
      '#size' => 25,
      '#theme_wrappers' => ['form_element'],
      '#theme' => 'input__textfield',
    ];

  }

  /**
   * Render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderBootstrapDate(array $element) {
    Element::setAttributes($element, [
      'id',
      'placeholder',
      'name',
      'value',
      'size',
      'required',
    ]);
    static::setAttributes($element, ['form-date']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processBootstrapDate(&$element, FormStateInterface $form_state, &$complete_form) {
    // Default settings.
    $default_settings = BootstrapDatepickerBase::defaultSettings();

    // Load the Bootstrap datepicker via data-provide attribute.
    $settings = ['data-provide' => 'datepicker'];

    // Load all bootstrap-datepicker settings in data-date attributes
    // only if they differ from default settings.
    foreach ($element as $key => $value) {
      $newKey = 'data-date-' . str_replace('_', '-', str_replace('#', '', $key));
      $dsKey = str_replace('#', '', $key);
      if (array_key_exists($dsKey, $default_settings)) {
        if ($default_settings[$dsKey] != $value) {
          // Javascript library needs 'true' or 'false in data- attribute.
          if ($element[$key] === '1') {
            $element[$key] = 'true';
          }
          if ($element[$key] === '0') {
            $element[$key] = 'false';
          }
          $settings[$newKey] = Html::escape($element[$key]);
        }
      }
    }

    // Load the Bootstrap datepicker title via data-provide attribute.
    $settings['data-date-title'] = $element['#datepicker_title'];

    // Append our attributes to element.
    $element['#attributes'] += $settings;

    // Disable autocomplete widget.
    $element['#attributes']['autocomplete'] = 'off';

    // Attach library.
    $element['#attached']['library'][] = 'bootstrap_datepicker/datepicker';

    // Attach language library.
    $element['#attached']['library'][] = 'bootstrap_datepicker/datepicker_' . $element['#language'];

    // If a field value is set, convert Drupal default date format
    // to format from field widget settings. This could be removed after
    // https://www.drupal.org/project/drupal/issues/2936268 is fixed.
    if (!empty($element['#value'])) {
      $new_date = new DrupalDateTime($element['#value']);
      // Convert javascript date format to PHP date format.
      $pattern = [
        '/(?<!m)m(?!m)/i',
        '/mm/i',
        '/(?<!d)d(?!d)/i',
        '/dd/i',
        '/yyyy/i',
        '/yy/i',
      ];
      $replacement = ['n', 'm', 'j', 'd', 'o', 'y'];
      $date_format = preg_replace($pattern, $replacement, $element['#format']);
      $element['#value'] = $new_date->format($date_format);
    }

    return $element;
  }

  /**
   * Return default settings. Pass in values to override defaults.
   *
   * @param array $values
   *   Some Desc.
   *
   * @return array
   *   Some Desc.
   */
  public static function settings(array $values = []) {
    $settings = [
      'lang' => 'en',
    ];

    return array_merge($settings, $values);
  }

}
