<?php

namespace Drupal\bootstrap_datepicker\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;

/**
 * Base class for SingleDateTime widget types.
 */
abstract class BootstrapDatepickerBase extends DateTimeWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->dateStorage = $date_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'assume_nearby_year' => FALSE,
      'autoclose' => FALSE,
      'container' => 'body',
      'calendar_weeks' => FALSE,
      'clear_btn' => FALSE,
      'toggle_active' => FALSE,
      'days_of_week_disabled' => [],
      'days_of_week_highlighted' => [],
      'dates_disabled' => '',
      'disable_touch_keyboard' => FALSE,
      'enable_on_readonly' => TRUE,
      'end_date_selection' => 'date',
      'end_date' => '31-12-2999',
      'end_date_timedelta' => '',
      'force_parse' => TRUE,
      'format' => '',
      'immediate_updates' => FALSE,
      'keep_empty_values' => FALSE,
      'keyboard_navigation' => TRUE,
      'language' => 'en',
      'min_view_mode' => '0',
      'max_view_mode' => '4',
      'multidate' => FALSE,
      'multidate_separator' => ',',
      'orientation' => 'auto',
      'rtl' => FALSE,
      'show_on_focus' => TRUE,
      'show_week_days' => TRUE,
      'start_date_selection' => 'date',
      'start_date' => '01-01-1000',
      'start_date_timedelta' => '',
      'start_view' => '0',
      'title' => '',
      'today_btn' => 'FALSE',
      'today_highlight' => FALSE,
      'update_view_date' => TRUE,
      'week_start' => '0',
      'z_index_offset' => 10,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['assume_nearby_year'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable nearby year assumption'),
      '#description' => $this->t('If true, manually-entered dates with two-digit years, such as “5/1/15”, will be parsed as “2015”, not “15”. If the year is less than 10 years in advance, the picker will use the current century, otherwise, it will use the previous one. For example “5/1/15” would parse to May 1st, 2015, but “5/1/97” would be May 1st, 1997.'),
      '#default_value' => $this->getSetting('assume_nearby_year'),
      '#required' => FALSE,
    ];
    $elements['autoclose'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable autoclose'),
      '#description' => $this->t('Whether or not to close the datepicker immediately when a date is selected.'),
      '#default_value' => $this->getSetting('autoclose'),
      '#required' => FALSE,
      '#weight' => '1',
      '#weight' => '-12',
    ];
    $elements['container'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container to be appended'),
      '#description' => $this->t('Appends the date picker popup to a specific element; eg: container: ‘#picker-container’ (will default to “body”)'),
      '#default_value' => $this->getSetting('container'),
      '#required' => TRUE,
      '#placeholder' => 'body',
    ];
    $elements['calendar_weeks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show calendar weeks'),
      '#description' => $this->t('Whether or not to show week numbers to the left of week rows.'),
      '#default_value' => $this->getSetting('calendar_weeks'),
      '#required' => FALSE,
    ];
    $elements['clear_btn'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show clear button'),
      '#description' => $this->t('Whether or not to show a “Clear” button at the bottom of the datepicker to clear the input value. If “autoclose” is also set to true, this button will also close the datepicker.'),
      '#default_value' => $this->getSetting('clear_btn'),
      '#required' => FALSE,
      '#weight' => '-14',
    ];
    $elements['toggle_active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Toggel active date'),
      '#description' => $this->t('When enabled, selecting the currently active date in the datepicker will unset the respective date. This option is always true when the multidate option is being used.'),
      '#default_value' => $this->getSetting('toggle_active'),
      '#required' => FALSE,
      '#suffix' => '<em>(Disabled. Will be enabled for comming features.)</em>',
      '#disabled' => TRUE,
    ];
    $elements['dates_disabled'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Dates disabled'),
      '#description' => $this->t('Enter a date on every new line formatted in the given date format.'),
      '#default_value' => $this->getSetting('dates_disabled'),
      '#rows' => '5',
      '#required' => FALSE,
    ];
    $elements['days_of_week_disabled'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Disable specific days in week'),
      '#description' => $this->t('Select days which are disabled in calendar.'),
      '#options' => [
        '1' => $this->t('Sunday'),
        '2' => $this->t('Monday'),
        '3' => $this->t('Tuesday'),
        '4' => $this->t('Wednesday'),
        '5' => $this->t('Thursday'),
        '6' => $this->t('Friday'),
        '7' => $this->t('Saturday'),
      ],
      '#default_value' => $this->getSetting('days_of_week_disabled'),
      '#required' => FALSE,
    ];
    $elements['days_of_week_highlighted'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Highlight specific days in week'),
      '#description' => $this->t('Select days which are highlightin calendar.'),
      '#options' => [
        '1' => $this->t('Sunday'),
        '2' => $this->t('Monday'),
        '3' => $this->t('Tuesday'),
        '4' => $this->t('Wednesday'),
        '5' => $this->t('Thursday'),
        '6' => $this->t('Friday'),
        '7' => $this->t('Saturday'),
      ],
      '#default_value' => $this->getSetting('days_of_week_highlighted'),
      '#required' => FALSE,
    ];
    $elements['disable_touch_keyboard'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable touch keyboard'),
      '#description' => $this->t('When enabled, no keyboard will show on mobile devices'),
      '#default_value' => $this->getSetting('disable_touch_keyboard'),
      '#required' => FALSE,
    ];
    $elements['enable_on_readonly'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable on readonly'),
      '#description' => $this->t('When enabled, the datepicker will also show on a readonly datepicker field.'),
      '#default_value' => $this->getSetting('enable_on_readonly'),
      '#required' => FALSE,
    ];
    $elements['end_date_selection'] = [
      '#type' => 'select',
      '#title' => $this->t('End date option'),
      '#description' => $this->t('The latest date that may be selected; all later dates will be disabled.'),
      '#options' => [
        'date' => $this->t('Date'),
        'timedelta' => $this->t('Timedelta'),
      ],
      '#default_value' => $this->getSetting('end_date_selection'),
      '#required' => TRUE,
    ];
    $elements['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End date'),
      '#default_value' => $this->getSetting('end_date'),
      '#states' => [
        'invisible' => [
          'select[name*="end_date_selection"]' => ['value' => 'timedelta'],
        ],
      ],
    ];
    $elements['end_date_timedelta'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date timedelta'),
      '#description' => $this->t('A timedelta relative to today, eg “-1d”, “+6m +1y”, etc, where valid units are “d” (day), “w” (week), “m” (month), and “y” (year). Use “0” as today. There are also aliases for the relative timedelta’s: “yesterday” equals “-1d”, “today” is equal to “+0d” and “tomorrow” is equal to “+1d”.'),
      '#default_value' => $this->getSetting('end_date_timedelta'),
      '#size' => 16,
      '#maxlength' => 10,
      '#states' => [
        'invisible' => [
          'select[name*="end_date_selection"]' => ['value' => 'date'],
        ],
      ],
    ];
    $elements['force_parse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force date parcing'),
      '#description' => $this->t('Whether or not to force parsing of the input value when the picker is closed.'),
      '#default_value' => $this->getSetting('force_parse'),
      '#required' => FALSE,
    ];
    $elements['format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#description' => $this->t('The date format, combination of d, dd, D, DD, m, mm, M, MM, yy, yyyy. For example dd/mm/yyyy'),
      '#default_value' => $this->getSetting('format'),
      '#size' => 10,
      '#maxlength' => 10,
      '#required' => TRUE,
      '#patern' => '{10}',
      '#placeholder' => 'yyyy-mm-dd',
      '#weight' => '-19',
    ];
    $elements['immediate_updates'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Immediate updates'),
      '#description' => $this->t('If true, selecting a year or month in the datepicker will update the input value immediately. Otherwise, only selecting a day of the month will update the input value immediately.'),
      '#default_value' => $this->getSetting('immediate_updates'),
      '#required' => FALSE,
    ];
    $elements['keep_empty_values'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Keep empty values'),
      '#description' => $this->t('Only effective in a range picker. If true, the selected value does not get propagated to other, currently empty, pickers in the range.'),
      '#default_value' => $this->getSetting('keep_empty_values'),
      '#required' => FALSE,
    ];
    $elements['keyboard_navigation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Keyboard navigation'),
      '#description' => $this->t('Whether or not to allow date navigation by arrow keys.'),
      '#default_value' => $this->getSetting('keyboard_navigation'),
      '#required' => FALSE,
    ];
    $elements['language'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Language'),
      '#description' => $this->t('The <abbr title="Internet Engineering Task Force">IETF</abbr> language tag. For example <em>de</em> or <em>en-UK</em>'),
      '#default_value' => $this->getSetting('language'),
      '#size' => 10,
      '#maxlength' => 2,
      '#required' => TRUE,
      '#patern' => '[a-z]{2}',
      '#placeholder' => 'en',
      '#weight' => '-18',
    ];
    $elements['max_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Max view mode'),
      '#description' => $this->t('Set a maximum limit for the view mode.'),
      '#options' => [
        '0' => $this->t('0 / days'),
        '1' => $this->t('1 / months'),
        '2' => $this->t('2 / years'),
        '3' => $this->t('3 / decade'),
        '4' => $this->t('4 / centuries'),
      ],
      '#default_value' => $this->getSetting('max_view_mode'),
      '#required' => FALSE,
    ];
    $elements['min_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Min view mode'),
      '#description' => $this->t('Set a minimum limit for the view mode.'),
      '#options' => [
        '0' => $this->t('0 / days'),
        '1' => $this->t('1 / months'),
        '2' => $this->t('2 / years'),
        '3' => $this->t('3 / decade'),
        '4' => $this->t('4 / centuries'),
      ],
      '#default_value' => $this->getSetting('min_view_mode'),
      '#required' => FALSE,
    ];
    $elements['multidate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Multiple dates'),
      '#description' => $this->t('Enable multidate picking. Each date in month view acts as a toggle button, keeping track of which dates the user has selected in order. If a number is given, the picker will limit how many dates can be selected to that number, dropping the oldest dates from the list when the number is exceeded.'),
      '#default_value' => $this->getSetting('multidate'),
      '#required' => FALSE,
      '#suffix' => '<em>(Disabled. Will be enabled for comming features.)</em>',
      '#disabled' => TRUE,
    ];
    $elements['multidate_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Multiple dates separator'),
      '#description' => $this->t('The string that will appear between dates when generating the input’s value. When parsing the input’s value for a multidate picker, this will also be used to split the incoming string to separate multiple formatted dates; as such, it is highly recommended that you not use a string that could be a substring of a formatted date (eg, using ‘-‘ to separate dates when your format is ‘yyyy-mm-dd’).'),
      '#default_value' => $this->getSetting('multidate_separator'),
      '#size' => 10,
      '#maxlength' => 1,
      '#required' => FALSE,
      '#patern' => '[^A-Za-z0-9]{1}',
      '#suffix' => '<em>(Disabled. Will be enabled for comming features.)</em>',
      '#disabled' => TRUE,
    ];
    $elements['orientation'] = [
      '#type' => 'select',
      '#title' => $this->t('Orientation'),
      '#description' => $this->t('“orientation” refers to the location of the picker popup’s “anchor”; you can also think of it as the location of the trigger element (input, component, etc) relative to the picker.'),
      '#options' => [
        'auto' => $this->t('auto'),
        'top auto' => $this->t('top auto'),
        'bottom auto' => $this->t('bottom auto'),
        'auto left' => $this->t('auto left'),
        'top left' => $this->t('top left'),
        'bottom left' => $this->t('bottom left'),
        'auto right' => $this->t('auto right'),
        'top right' => $this->t('top right'),
        'bottom right' => $this->t('bottom right'),
      ],
      '#default_value' => $this->getSetting('orientation'),
      '#required' => FALSE,
    ];
    $elements['rtl'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Languages direction'),
      '#description' => $this->t('Set to true if you are using a RTL language.'),
      '#default_value' => $this->getSetting('rtl'),
      '#required' => FALSE,
      '#weight' => '-17',
    ];
    $elements['show_on_focus'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show on focus'),
      '#description' => $this->t('If false, the datepicker will be prevented from showing when the input field associated with it receives focus.'),
      '#default_value' => $this->getSetting('show_on_focus'),
      '#required' => FALSE,
    ];
    $elements['show_week_days'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show week days'),
      '#description' => $this->t('When enabled, the datepicker will append the names of the weekdays to its view.'),
      '#default_value' => $this->getSetting('show_week_days'),
      '#required' => FALSE,
      '#weight' => '-16',
    ];
    $elements['start_date_selection'] = [
      '#type' => 'select',
      '#title' => $this->t('Start date option'),
      '#description' => $this->t('The earliest date that may be selected; all earlier dates will be disabled.'),
      '#options' => [
        'date' => $this->t('Date'),
        'timedelta' => $this->t('Timedelta'),
      ],
      '#default_value' => $this->getSetting('start_date_selection'),
      '#required' => TRUE,
    ];
    $elements['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start date'),
      '#default_value' => $this->getSetting('start_date'),
      '#states' => [
        'invisible' => [
          'select[name*="start_date_selection"]' => ['value' => 'timedelta'],
        ],
      ],
    ];
    $elements['start_date_timedelta'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date timedelta'),
      '#description' => $this->t('A timedelta relative to today, eg “-1d”, “+6m +1y”, etc, where valid units are “d” (day), “w” (week), “m” (month), and “y” (year). Use “0” as today. There are also aliases for the relative timedelta’s: “yesterday” equals “-1d”, “today” is equal to “+0d” and “tomorrow” is equal to “+1d”.'),
      '#default_value' => $this->getSetting('start_date_timedelta'),
      '#size' => 16,
      '#maxlength' => 10,
      '#states' => [
        'invisible' => [
          'select[name*="start_date_selection"]' => ['value' => 'date'],
        ],
      ],
    ];
    $elements['start_view'] = [
      '#type' => 'select',
      '#title' => $this->t('Start view'),
      '#description' => $this->t('The view that the datepicker should show when it is opened.'),
      '#options' => [
        '0' => $this->t('0 / days'),
        '1' => $this->t('1 / months'),
        '2' => $this->t('2 / years'),
        '3' => $this->t('3 / decade'),
        '4' => $this->t('4 / centuries'),
      ],
      '#default_value' => $this->getSetting('start_view'),
      '#required' => FALSE,
    ];
    $elements['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Datepicker title'),
      '#description' => $this->t('A title that will appear on top of the datepicker. If empty the title will be hidden.'),
      '#default_value' => $this->getSetting('title'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => FALSE,
      '#patern' => '[a-zA-Z0-9]+',
      '#weight' => '-20',
    ];
    $elements['today_btn'] = [
      '#type' => 'select',
      '#title' => $this->t('Show today button'),
      '#description' => $this->t('If true, the “Today” button will move the current date into view, it will not select the current date.'),
      '#options' => [
        'FALSE' => $this->t('No'),
        'TRUE' => $this->t('Yes'),
        'linked' => $this->t('Linked'),
      ],
      '#default_value' => $this->getSetting('today_btn'),
      '#required' => FALSE,
      '#weight' => '-15',
    ];
    $elements['today_highlight'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Highlight today'),
      '#description' => $this->t('When enabled, highlights the current date.'),
      '#default_value' => $this->getSetting('today_highlight'),
      '#required' => FALSE,
    ];
    $elements['update_view_date'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Update view date'),
      '#default_value' => $this->getSetting('update_view_date'),
      '#required' => FALSE,
    ];
    $elements['week_start'] = [
      '#type' => 'select',
      '#title' => $this->t('Week start'),
      '#description' => $this->t('Day of the week start.'),
      '#options' => [
        '0' => $this->t('Sunday'),
        '1' => $this->t('Monday'),
        '2' => $this->t('Tuesday'),
        '3' => $this->t('Wednesday'),
        '4' => $this->t('Thursday'),
        '5' => $this->t('Friday'),
        '6' => $this->t('Saturday'),
      ],
      '#default_value' => $this->getSetting('week_start'),
      '#required' => FALSE,
      '#weight' => '-10',
    ];
    $elements['z_index_offset'] = [
      '#type' => 'number',
      '#title' => $this->t('Z-index offset'),
      '#description' => $this->t('The CSS z-index of the open datepicker is the maximum z-index of the input and all of its DOM ancestors plus this value'),
      '#default_value' => $this->getSetting('z_index_offset'),
      '#required' => FALSE,
      '#placeholder' => 10,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // Default settings.
    $default_settings = BootstrapDatepickerBase::defaultSettings();

    $today_options = [
      'FALSE' => $this->t('No'),
      'TRUE' => $this->t('Yes'),
      'linked' => $this->t('Linked'),
    ];

    $orientation_options = [
      'auto' => $this->t('auto'),
      'top auto' => $this->t('top auto'),
      'bottom auto' => $this->t('bottom auto'),
      'auto left' => $this->t('auto left'),
      'top left' => $this->t('top left'),
      'bottom left' => $this->t('bottom left'),
      'auto right' => $this->t('auto right'),
      'top right' => $this->t('top right'),
      'bottom right' => $this->t('bottom right'),
    ];

    $day_options = [
      '0' => $this->t('Sunday'),
      '1' => $this->t('Monday'),
      '2' => $this->t('Tuesday'),
      '3' => $this->t('Wednesday'),
      '4' => $this->t('Thursday'),
      '5' => $this->t('Friday'),
      '6' => $this->t('Saturday'),
    ];

    $period_options = [
      '0' => $this->t('0 / days'),
      '1' => $this->t('1 / months'),
      '2' => $this->t('2 / years'),
      '3' => $this->t('3 / decade'),
      '4' => $this->t('4 / centuries'),
    ];

    $days_of_week_disabled = [];
    foreach ($this->getSetting('days_of_week_disabled') as $key => $value) {
      if (!empty($value)) {
        // We need to re-index to 0
        // because checkboxes array started with 1 and not 0.
        $days_of_week_disabled[] = $day_options[($value - 1)];
      }
    }
    $days_of_week_disabled = implode(', ', $days_of_week_disabled);

    $days_of_week_highlighted = [];
    foreach ($this->getSetting('days_of_week_highlighted') as $key => $value) {
      if (!empty($value)) {
        // We need to re-index to 0
        // because checkboxes array started with 1 and not 0.
        $days_of_week_highlighted[] = $day_options[($value - 1)];
      }
    }
    $days_of_week_highlighted = implode(', ', $days_of_week_highlighted);

    $orientation = $orientation_options[$this->getSetting('orientation')];
    $start_view = $day_options[$this->getSetting('start_view')];
    $week_start = $day_options[$this->getSetting('week_start')];
    $min_view_mode = $period_options[$this->getSetting('min_view_mode')];
    $max_view_mode = $period_options[$this->getSetting('max_view_mode')];
    $today_btn = $today_options[$this->getSetting('today_btn')];

    if ($this->getSetting('end_date_selection') == 'date') {
      $selected_end_date = $this->getSetting('end_date');
    }
    elseif ($this->getSetting('end_date_selection') == 'timedelta') {
      $selected_end_date = $this->getSetting('end_date_timedelta');
    }

    if ($this->getSetting('start_date_selection') == 'date') {
      $selected_start_date = $this->getSetting('start_date');
    }
    elseif ($this->getSetting('start_date_selection') == 'timedelta') {
      $selected_start_date = $this->getSetting('start_date_timedelta');
    }

    $summary = [];
    $summary[] = $this->t('<strong>Basic settings:</strong>');
    $summary[] = $this->t('Datepicker title: @title', ['@title' => !empty($this->getSetting('title')) ? $this->getSetting('title') : t('None')]);
    $summary[] = $this->t('Date format: @format', ['@format' => !empty($this->getSetting('format')) ? $this->getSetting('format') : t('Empty')]);
    $summary[] = $this->t('Language tag: @language', ['@language' => !empty($this->getSetting('language')) ? $this->getSetting('language') : t('Empty')]);
    $summary[] = $this->t('RTL language: @rtl', ['@rtl' => !empty($this->getSetting('rtl')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Show week days: @show_week_days', ['@show_week_days' => !empty($this->getSetting('show_week_days')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Show today button: @today_btn', ['@today_btn' => $today_btn]);
    $summary[] = $this->t('Week start: @week_start', ['@week_start' => $week_start]);
    $summary[] = $this->t('Enable autoclose: @autoclose', ['@autoclose' => !empty($this->getSetting('autoclose')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Show clear button: @clear_btn', ['@clear_btn' => !empty($this->getSetting('clear_btn')) ? t('Yes') : t('No')]);

    $summary[] = $this->t('<strong>Advanced settings:</strong>');

    $summary[] = $this->t('Nearby year assumption: @assume_nearby_year', ['@assume_nearby_year' => !empty($this->getSetting('assume_nearby_year')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Container to be appended: <code>@container</code>', ['@container' => !empty($this->getSetting('container')) ? $this->getSetting('container') : t('None')]);
    $summary[] = $this->t('Show calendar weeks: @calendar_weeks', ['@calendar_weeks' => !empty($this->getSetting('calendar_weeks')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Toggel active date: @toggle_active', ['@toggle_active' => !empty($this->getSetting('toggle_active')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Dates disabled: @dates_disabled', ['@dates_disabled' => !empty($this->getSetting('dates_disabled')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Disabled days in week: @days_of_week_disabled', ['@days_of_week_disabled' => $days_of_week_disabled]);
    $summary[] = $this->t('Highlighted days in week: @days_of_week_highlighted', ['@days_of_week_highlighted' => $days_of_week_highlighted]);
    $summary[] = $this->t('Disable touch keyboard: @disable_touch_keyboard', ['@disable_touch_keyboard' => !empty($this->getSetting('disable_touch_keyboard')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Enabled when readonly: @enable_on_readonly', ['@enable_on_readonly' => !empty($this->getSetting('enable_on_readonly')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('End date: @end_date', ['@end_date' => !empty($selected_end_date) ? $selected_end_date : t('Infinity')]);
    $summary[] = $this->t('Force date parcing: @force_parse', ['@force_parse' => !empty($this->getSetting('force_parse')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Immediate updates: @immediate_updates', ['@immediate_updates' => !empty($this->getSetting('immediate_updates')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Keep empty values: @keep_empty_values', ['@keep_empty_values' => !empty($this->getSetting('keep_empty_values')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Keyboard navigation: @keyboard_navigation', ['@keyboard_navigation' => !empty($this->getSetting('keyboard_navigation')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Max view mode: @max_view_mode', ['@max_view_mode' => $max_view_mode]);
    $summary[] = $this->t('Min view mode: @min_view_mode', ['@min_view_mode' => $min_view_mode]);
    $summary[] = $this->t('Multiple dates: @multidate', ['@multidate' => !empty($this->getSetting('multidate')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Multiple dates separator: <code>@multidate_separator</code>', ['@multidate_separator' => !empty($this->getSetting('multidate_separator')) ? $this->getSetting('multidate_separator') : t('None')]);
    $summary[] = $this->t('Orientation: @orientation', ['@orientation' => $orientation]);
    $summary[] = $this->t('Start date: @start_date', ['@start_date' => !empty($selected_start_date) ? $selected_start_date : t('Infinity')]);
    $summary[] = $this->t('Start view: @start_view', ['@start_view' => $start_view]);
    $summary[] = $this->t('Highlight today: @today_highlight', ['@today_highlight' => !empty($this->getSetting('today_highlight')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Update view date: @update_view_date', ['@update_view_date' => !empty($this->getSetting('update_view_date')) ? t('Yes') : t('No')]);
    $summary[] = $this->t('Z-index offset: @z_index_offset', ['@z_index_offset' => !empty($this->getSetting('z_index_offset')) ? $this->getSetting('z_index_offset') : t('None')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timezone and format.
    foreach ($values as &$item) {

      if (!empty($item['value'])) {
        // Date value is now string not instance of DrupalDateTime (without T).
        // String needs to be converted to DrupalDateTime.
        $start_date = new DrupalDateTime($item['value']);
        switch ($this->getFieldSetting('datetime_type')) {
          // Dates only.
          case DateTimeItem::DATETIME_TYPE_DATE:
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($start_date);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          // All day.
          case DateRangeItem::DATETIME_TYPE_ALLDAY:
            // All day fields start at midnight on the starting date, but are
            // stored like datetime fields, so we need to adjust the time.
            // This function is called twice, so to prevent a double conversion
            // we need to explicitly set the timezone.
            $start_date->setTimeZone(timezone_open(drupal_get_user_timezone()));
            $start_date->setTime(0, 0, 0);
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;

          // Date and time.
          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['value'] = $start_date->format($format);
      }

      // This is case for daterange field.
      if (!empty($item['end_value'])) {

        // Convert string to DrupalDateTime.
        $end_date = new DrupalDateTime($item['end_value']);
        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($end_date);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          case DateRangeItem::DATETIME_TYPE_ALLDAY:
            // All day fields end at midnight on the end date, but are
            // stored like datetime fields, so we need to adjust the time.
            // This function is called twice, so to prevent a double conversion
            // we need to explicitly set the timezone.
            $end_date->setTimeZone(timezone_open(drupal_get_user_timezone()));
            $end_date->setTime(23, 59, 59);
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;

          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $end_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['end_value'] = $end_date->format($format);
      }
    }
    return $values;
  }

  /**
   * Creates a date string for use as a default value.
   *
   * This will take a default value, apply the proper timezone for display in
   * a widget, and set the default time for date-only fields.
   *
   * @param object $date
   *   The UTC default date.
   * @param string $timezone
   *   The timezone to apply.
   * @param string $format
   *   Date format to apply.
   *
   * @return string
   *   String for use as a default value in a field widget.
   */
  public function formatDefaultValue($date, $timezone, $format) {
    // The date was created and verified during field_load(), so it is safe to
    // use without further inspection.
    if ($this->getFieldSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      // A date without time will pick up the current time, use the default
      // time.
      datetime_date_default_time($date);
    }
    $date->setTimezone(new \DateTimeZone($timezone));

    // Format date.
    return $date->format($format);
  }

  /**
   * Return array of field settings.
   *
   * @return array
   *   Formatted array of all available settings.
   */
  public function getCommonElementSettings() {

    $days_of_week_disabled = [];
    foreach ($this->getSetting('days_of_week_disabled') as $key => $value) {
      if (!empty($value)) {
        // We need to re-index to 0
        // because checkboxes array started with 1 and not 0.
        $days_of_week_disabled[] = ($value - 1);
      }
    }
    $days_of_week_disabled = '[' . implode(',', $days_of_week_disabled) . ']';

    $days_of_week_highlighted = [];
    foreach ($this->getSetting('days_of_week_highlighted') as $key => $value) {
      if (!empty($value)) {
        // We need to re-index to 0
        // because checkboxes array started with 1 and not 0.
        $days_of_week_highlighted[] = ($value - 1);
      }
    }
    $days_of_week_highlighted = '[' . implode(',', $days_of_week_highlighted) . ']';

    $selected_end_date = '';
    if ($this->getSetting('end_date_selection') == 'date') {
      // Javascript library doesn't understand date format Y-m-d.
      if (!empty($this->getSetting('end_date'))) {
        $new_end_date = new DrupalDateTime($this->getSetting('end_date'));
        $selected_end_date = $new_end_date->format('d-m-Y');
      }
    }
    elseif ($this->getSetting('end_date_selection') == 'timedelta') {
      $selected_end_date = $this->getSetting('end_date_timedelta');
    }

    $selected_start_date = '';
    if ($this->getSetting('start_date_selection') == 'date') {
      if (!empty($this->getSetting('start_date'))) {
        $new_start_date = new DrupalDateTime($this->getSetting('start_date'));
        $selected_start_date = $new_start_date->format('d-m-Y');
      }
    }
    elseif ($this->getSetting('start_date_selection') == 'timedelta') {
      $selected_start_date = $this->getSetting('start_date_timedelta');
    }

    return [
      '#assume_nearby_year' => $this->getSetting('assume_nearby_year'),
      '#autoclose' => $this->getSetting('autoclose'),
      '#container' => $this->getSetting('container'),
      '#calendar_weeks' => $this->getSetting('calendar_weeks'),
      '#clear_btn' => $this->getSetting('clear_btn'),
      '#days_of_week_disabled' => $days_of_week_disabled,
      '#mask' => $this->getSetting('mask'),
      '#datetimepicker_theme' => $this->getSetting('datetimepicker_theme'),
      '#days_of_week_highlighted' => $days_of_week_highlighted,
      '#dates_disabled' => $this->getSetting('dates_disabled'),
      '#disable_touch_keyboard' => $this->getSetting('disable_touch_keyboard'),
      '#enable_on_readonly' => $this->getSetting('enable_on_readonly'),
      '#end_date' => $selected_end_date,
      '#force_parse' => $this->getSetting('force_parse'),
      '#format' => $this->getSetting('format'),
      '#immediate_updates' => $this->getSetting('immediate_updates'),
      '#keep_empty_values' => $this->getSetting('keep_empty_values'),
      '#keyboard_navigation' => $this->getSetting('keyboard_navigation'),
      '#language' => $this->getSetting('language'),
      '#min_view_mode' => $this->getSetting('min_view_mode'),
      '#max_view_mode' => $this->getSetting('max_view_mode'),
      '#multidate' => $this->getSetting('multidate'),
      '#multidate_separator' => $this->getSetting('multidate_separator'),
      '#orientation' => $this->getSetting('orientation'),
      '#rtl' => $this->getSetting('rtl'),
      '#show_on_focus' => $this->getSetting('show_on_focus'),
      '#show_week_days' => $this->getSetting('show_week_days'),
      '#start_date' => $selected_start_date,
      '#start_view' => $this->getSetting('start_view'),
      '#datepicker_title' => $this->getSetting('title'),
      '#today_btn' => $this->getSetting('today_btn'),
      '#toggle_active' => $this->getSetting('toggle_active'),
      '#today_highlight' => $this->getSetting('today_highlight'),
      '#update_view_date' => $this->getSetting('update_view_date'),
      '#week_start' => $this->getSetting('week_start'),
      '#z_index_offset' => $this->getSetting('z_index_offset'),
    ];
  }

}
