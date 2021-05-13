<?php

namespace Drupal\music_search;


use Drupal\Core\Config\ConfigFactoryInterface;
use http\Message\Body;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SpotifySearchService {

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
    $user_input = $config->get("spotify_search");
    $radio_button_value = $config->get("rad_val");
    $query_string = "https://api.spotify.com/v1/search?q=" . $user_input .  "&type=" . $radio_button_value;
    return $this->_spotify_api_get_query($query_string);
  }


  /**
   * Sends a GET query to Spotify for specific URL
   *
   * @param $uri string
   *   The fully generated search string
   * @return object
   *   Returns a stdClass with the search results or an error message
   */
  function _spotify_api_get_query($uri) {
    $token = $this->_spotify_api_get_auth_token();
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
  private function _spotify_api_get_auth_token() {
    $SPOTIFY_API_CLIENT_ID = "529fd7ae993c488383c2700160208bbf";
    $SPOTIFY_API_CLIENT_SECRET = "7a1c8b560e5b426b978963b897a1b6a7";
    $connection_string = "https://accounts.spotify.com/api/token";
    $key = base64_encode($SPOTIFY_API_CLIENT_ID . ':' . $SPOTIFY_API_CLIENT_SECRET);
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

//  /**
//   * Saves a search to Drupal's internal cache.
//   *
//   * @param string $cid
//   *   The cache id to use.
//   * @param array $data
//   *   The data to cache.
//   */
//  function _spotify_api_set_cache_search($cid, array $data) {
//    cache_set($cid, $data, 'spotify-api-cache', time() + SPOTIFY_CACHE_LIFETIME);
//  }
//
//  /**
//   * Looks up the specified cid in cache and returns if found
//   *
//   * @param string $cid
//   *   Normally a uri with a search string
//   *
//   * @return array|bool
//   *   Returns either the cache results or false if nothing is found.
//   */
//  function _spotify_api_get_cache_search($cid) {
//    $cache = cache_get($cid, 'spotify-api-cache');
//    if (!empty($cache)) {
//      if ($cache->expire > time()) {
//        return $cache->data;
//      }
//    }
//    return FALSE;
//  }
