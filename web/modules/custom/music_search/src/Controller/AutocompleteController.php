<?php

namespace Drupal\music_search\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\music_search\SpotifySearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;

class AutocompleteController extends ControllerBase {

  /**
   * @var \Drupal\music_search\SpotifySearchService
   */
  protected $spotify_service;

  /**
   * SearchController constructor.
   *
   * @param \Drupal\music_search\SpotifySearchService $spotify_service
   */
  public function __construct(SpotifySearchService $spotify_service) {
    $this->spotify_service = $spotify_service;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get("music_search.search")
    );
  }

  public function handleAutocomplete(Request $request) {
    $results = [];
    $input = $request->query->get("q");
    if (!$input) {
      return new JsonResponse($results);
    }
    $uri = "https://api.spotify.com/v1/search?q=" . $input .  "&type=album,artist,track";
    $json_obj = json_decode($this->spotify_service->_spotify_api_get_query($uri));

    $results = [];
    $counter = 1;
    foreach($json_obj as $property) {
      foreach($property->items as $item) {
        $search_result = $item->name;
        if ($counter <= 10) {
          array_push($results, $search_result);
          $counter += 1;
        }
      }
    }
    return new JsonResponse($results);
  }

}
