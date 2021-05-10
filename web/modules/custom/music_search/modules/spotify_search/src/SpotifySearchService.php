<?php

namespace Drupal\music_search\modules\spotify_search;

use http\Message\Body;

class SpotifySearchService {
  /**
   * Sends a GET query to Spotify for specific URL
   *
   * @param $uri string
   *   The fully generated search string
   * @return object
   *   Returns a stdClass with the search results or an error message
   */
  function _spotify_api_get_query($uri) {
//    $cache = $this->_spotify_api_get_cache_search($uri);
    $search_results = null;
//
//    if (!empty($cache)) {
//      $search_results = $cache;
//    }
//    else {
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

      $client =  \Drupal::httpClient();
      $response = $client -> get($uri, $options);
      #$search_results = $response->getBody();     #data



      if (empty($search_results->error)) {
        $search_results = json_decode($response->getBody());
        //$this->_spotify_api_set_cache_search($uri, $search_results);

      }
      else {
//        drupal_set_message(t('The search request resulted in the following error: @error.', array(
//          '@error' => $response->error,
//        )));


        \Drupal::messenger()->addMessage(t('The search request resulted in the following error: @error', array('@error' => $response->error,
          )), 'error');
        return $search_results->error;
      }
    return $search_results;
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
