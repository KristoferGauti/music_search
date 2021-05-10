<?php
namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\music_search\modules\spotify_search\SpotifySearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SearchController
 *
 * @package Drupal\music_search\Controller
 */
class SearchController extends ControllerBase {

  protected $spotifyService;

  public function __construct(SpotifySearchService $spotifyService) {
    $this->spotifyService = $spotifyService;
  }

  static function create(ContainerInterface $container) {
    return new static (
      $container->get("spotify.search") //name of the service
    );
  }

  public function search_results() {
    return [
      "#markup" => $this->spotifyService->_spotify_api_get_query("Queen")
    ];
  }
}
