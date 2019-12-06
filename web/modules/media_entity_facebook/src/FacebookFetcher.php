<?php

namespace Drupal\media_entity_facebook;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;

/**
 * Class FacebookFetcher.
 */
class FacebookFetcher {

  /**
   * Stores logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $loggerChannel;

  /**
   * Guzzle HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Tracks when an error has occurred when interacting with the API.
   *
   * @var bool
   */
  protected $apiErrorEncountered = FALSE;

  /**
   * FacebookFetcher constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger factory.
   * @param \GuzzleHttp\ClientInterface $client
   *   The Guzzle HTTP client.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory, ClientInterface $client) {
    $this->loggerChannel = $logger_channel_factory->get('media_entity_facebook');
    $this->httpClient = $client;
  }

  /**
   * Fetch and return response from Facebook's oEmbed API endpoint.
   *
   * @param string $resource_url
   *   The URL to pass to Facebook's oembed API.
   */
  public function getOembedData($resource_url) {
    // Keep an in-memory cache of the response data for each URL, since this
    // data may be requested multiple times on the same request.
    static $memory_cache;
    if (!isset($memory_cache)) {
      $memory_cache = [];
    }

    if (!isset($memory_cache[$resource_url])) {
      // If there was an error interacting with the Facebook API, like a network
      // timeout due to Facebook being down, we don't want to clog up the Drupal
      // site's resources by making lots of API requests that may all timeout.
      // To do this, we mark when a request exception occurred and back out of
      // subsequent requests if so.
      if ($this->apiErrorEncountered) {
        $this->loggerChannel->error('Aborting Facebook API request due to previously encountered error.');
        return FALSE;
      }

      $endpoint = $this->getApiEndpointUrl($resource_url) . '?url=' . $resource_url;

      try {
        $response = $this->httpClient->request('GET', $endpoint, ['timeout' => 5]);
        $decoded = json_decode((string) $response->getBody(), TRUE);
        $memory_cache[$resource_url] = $decoded;
      }
      catch (TransferException $e) {
        \Drupal::logger('media_entity_facebook')->error('Error retrieving oEmbed data for a Facebook media entity: @error', ['@error' => $e->getMessage()]);
        $this->apiErrorEncountered = TRUE;
        return FALSE;
      }
    }
    return $memory_cache[$resource_url];
  }

  /**
   * Return the appropriate Facebook oEmbed API endpoint for the content URL.
   *
   * @param string $content_url
   *   The content URL contains the URL to the resource.
   *
   * @return string
   *   The oEmbed endpoint URL.
   */
  protected function getApiEndpointUrl($content_url) {
    if (preg_match('/\/videos\//', $content_url) || preg_match('/\/video.php\//', $content_url)) {
      return 'https://www.facebook.com/plugins/video/oembed.json/';
    }
    else {
      return 'https://www.facebook.com/plugins/post/oembed.json/';
    }
  }

}
