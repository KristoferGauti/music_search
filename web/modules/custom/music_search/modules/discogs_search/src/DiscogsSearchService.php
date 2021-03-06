<?php

namespace Drupal\music_search\discogs_search;


use Drupal\Core\Config\ConfigFactoryInterface;
use http\Message\Body;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DiscogsSearchService {

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function get_radio_button_value() {
    return $this->configFactory->get("music_search.search")->get("rad_val");
  }

  public function get_data() {
    $config = $this->configFactory->get("music_search.search");
    $user_input = $config->get("search_value");
    $radio_button_value = $config->get("rad_val");
    $query_string = "https://api.spotify.com/v1/search?q=" . $user_input .  "&type=" . $radio_button_value;
    return $this->_discogs_api_get_query($query_string);
  }


  /**
   * Sends a GET query to Discogs for specific URL
   *
   * @param $uri string
   *   The fully generated search string
   * @return object
   *   Returns a stdClass with the search results or an error message
   */
  function _discogs_api_get_query($uri) {
    $token = $this->_discogs_api_get_auth_token();
    $token = json_decode($token);
    $options = array(
      'method' => 'GET',
      'timeout' => 3,
      'headers' => array(
        'Accept' => 'application/json',
        'Authorization' => "Bearer " . $token->access_token,
      ),
    );

    $client = \Drupal::httpClient();
    $response = $client->get($uri, $options);
    return $response->getBody();
  }

  /**
   * Gets Auth token from the Spotify API
   */
  private function _discogs_api_get_auth_token() {
    $DISCOGS_API_CONSUMER_KEY = "xfGhpnxtocXhdbPTeFeE";
    $DISCOGS_API_CONSUMER_SECRET = "iOLmwrmzgpBQeKjycHakmlIJFGaGOBiQ";
    $connection_string = "https://accounts.spotify.com/api/token";
    $key = base64_encode($DISCOGS_API_CONSUMER_KEY . ':' . $DISCOGS_API_CONSUMER_SECRET);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $connection_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_POST, 1);

    $headers = array();
    $headers[] = "Authorization: Basic " . $key;
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    curl_close ($ch);
    return $result;
  }
}
