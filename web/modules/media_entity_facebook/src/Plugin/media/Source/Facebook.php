<?php

namespace Drupal\media_entity_facebook\Plugin\media\Source;

use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaSourceFieldConstraintsInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\media\MediaTypeInterface;
use Drupal\media_entity_facebook\FacebookFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Facebook entity media source.
 *
 * @MediaSource(
 *   id = "facebook",
 *   label = @Translation("Facebook"),
 *   description = @Translation("Provides business logic and metadata for Facebook."),
 *   allowed_field_types = {"string_long"},
 *   default_thumbnail_filename = "facebook.png"
 * )
 */
class Facebook extends MediaSourceBase implements MediaSourceFieldConstraintsInterface {

  /**
   * Facebook Fetcher.
   *
   * @var \Drupal\media_entity_facebook\FacebookFetcher
   */
  protected $facebookFetcher;

  /**
   * Constructs Facebook media source.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type plugin manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\media_entity_facebook\FacebookFetcher $facebook_fetcher
   *   The facebook fetcher.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, FieldTypePluginManagerInterface $field_type_manager, ConfigFactoryInterface $config_factory, FacebookFetcher $facebook_fetcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $field_type_manager, $config_factory);
    $this->facebookFetcher = $facebook_fetcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('config.factory'),
      $container->get('media_entity_facebook.facebook_fetcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    $attributes = [
      'author_name' => $this->t('Author Name'),
      'width' => $this->t('Width'),
      'height' => $this->t('Height'),
      'url' => $this->t('URL'),
      'html' => $this->t('HTML'),
    ];

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    $content_url = $this->getFacebookUrl($media);
    if ($content_url === FALSE) {
      return FALSE;
    }

    $data = $this->facebookFetcher->getOembedData($content_url);
    if ($data === FALSE) {
      return FALSE;
    }

    switch ($attribute_name) {
      case 'author_name':
        return $data['author_name'];

      case 'width':
        return $data['width'];

      case 'height':
        return $data['height'];

      case 'url':
        return $data['url'];

      case 'html':
        return $data['html'];

      default:
        return parent::getMetadata($media, $attribute_name);
    }
  }

  /**
   * Runs preg_match on embed code/URL.
   *
   * @param \Drupal\media\MediaInterface $media
   *   Media object.
   *
   * @return string|false
   *   The facebook url or FALSE if there is no field or it contains invalid
   *   data.
   */
  protected function getFacebookUrl(MediaInterface $media) {
    if (isset($this->configuration['source_field'])) {
      $source_field = $this->configuration['source_field'];
      if ($media->hasField($source_field)) {
        $property_name = $media->{$source_field}->first()->mainPropertyName();
        $embed = $media->{$source_field}->{$property_name};

        return static::parseFacebookEmbedField($embed);
      }
    }

    return FALSE;
  }

  /**
   * Extract a Facebook content URL from a string.
   *
   * Typically users will enter an iframe embed code that Facebook provides, so
   * which needs to be parsed to extract the actual post URL.
   *
   * Users may also enter the actual content URL - in which case we just return
   * the value if it matches our expected format.
   *
   * @param string $data
   *   The string that contains the Facebook post URL.
   *
   * @return string|bool
   *   The post URL, or FALSE if one cannot be found.
   */
  public static function parseFacebookEmbedField($data) {
    $data = trim($data);

    // Ideally we would verify that the content URL matches an exact pattern,
    // but Facebook has a ton of different ways posts/notes/videos/etc URLs can
    // be formatted, so it's not practical to try and validate them. Instead,
    // just validate that the content URL is from the facebook domain.
    $content_url_regex = '/^https:\/\/(www\.)?facebook\.com\//i';

    if (preg_match($content_url_regex, $data)) {
      return $data;
    }
    else {
      // Check if the user entered an iframe embed instead, and if so,
      // extract the post URL from the iframe src.
      $doc = new \DOMDocument();
      if (@$doc->loadHTML($data)) {
        $iframes = $doc->getElementsByTagName('iframe');
        if ($iframes->length > 0 && $iframes->item(0)->hasAttribute('src')) {
          $iframe_src = $iframes->item(0)->getAttribute('src');
          $uri_parts = parse_url($iframe_src);
          if ($uri_parts !== FALSE && isset($uri_parts['query'])) {
            parse_str($uri_parts['query'], $query_params);
            if (isset($query_params['href']) && preg_match($content_url_regex, $query_params['href'])) {
              return $query_params['href'];
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFieldConstraints() {
    return ['FacebookEmbedCode' => []];
  }

}
