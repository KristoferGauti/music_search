<?php

namespace Drupal\music_search\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\music_search\DiscogsSearchService;
use Drupal\music_search\SpotifySearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class AutocompleteController extends ControllerBase {

  /**
   * @var \Drupal\music_search\SpotifySearchService
   */
  protected $spotify_service;
  protected $discogs_service;

  /**
   * SearchController constructor.
   *
   * @param \Drupal\music_search\SpotifySearchService $spotify_service
   */
  public function __construct(SpotifySearchService $spotify_service, DiscogsSearchService $discogs_service) {
    $this->spotify_service = $spotify_service;
    $this->discogs_service = $discogs_service;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get("music_search.search"),
      $container->get("discogs_search.search")
    );
  }

  public function handleAutocomplete(Request $request) {
    $results = [];
    $type = $this->spotify_service->get_radio_button_value();
    $input = $request->query->get("q");
    if (!$input) {
      return new JsonResponse($results);
    }
    $spotify_uri = "https://api.spotify.com/v1/search?q=" . $input .  "&type=" . $type;
    //$discogs_uri =
    $json_obj_spotify = json_decode($this->spotify_service->_spotify_api_get_query($spotify_uri));
    //$json_obj_discogs = json_decode($this->discogs_service->_discogs_api_get_query($discogs_uri));

    $results = [];
    $counter = 1;
    foreach($json_obj_spotify as $property) {
      foreach($property->items as $item) {
        $search_result = $item->name . " - Spotify";
        if ($counter <= 100) {
          array_push($results, $search_result);
          $counter += 1;
        }
      }
    }
    return new JsonResponse($results);
  }

}
