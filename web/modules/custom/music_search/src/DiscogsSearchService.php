<?php

namespace Drupal\music_search;


use Drupal\Core\Config\ConfigFactoryInterface;

class DiscogsSearchService {

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function get_data() {
    $config = $this->configFactory->get("music_search.search");
    $user_input = $config->get("spotify_search");
    $radio_button_value = $config->get("rad_val");
    $query_string = "https://api.discogs.com/database/search?q=". $user_input . "&" . $radio_button_value;
    $all_items = $this->_discogs_api_get_query($query_string);
    return json_decode($all_items)->results;
  }


  /**
   * Sends a GET query to Spotify for specific URL
   *
   * @param $uri string
   *   The fully generated search string
   * @return object
   *   Returns a stdClass with the search results or an error message
   */
  function _discogs_api_get_query($uri) {
    $custom_config = \Drupal::config("music_search.settings");
    $DISCOGS_CONSUMER_KEY = $custom_config->get("discogs_consumer_key");
    $DISCOGS_CONSUMER_SECRET = $custom_config->get("discogs_consumer_secret");

    $options = array(
      'method' => 'GET',
      'timeout' => 3,
      'headers' => array(
        'Accept' => 'application/json',
        'Authorization' => "Discogs key=" . $DISCOGS_CONSUMER_KEY . ", secret=". $DISCOGS_CONSUMER_SECRET ,
      ),
    );

    $client = \Drupal::httpClient();
    $response = $client->get($uri, $options);
    return $response->getBody();
  }

}


