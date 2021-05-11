<?php
namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\music_search\SpotifySearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SearchController
 *
 * @package Drupal\music_search\Controller
 */
class SearchController extends ControllerBase {
  /**
   * The search service
   * @var \Drupal\music_search\SpotifySearchService
   *
   */
  protected $spotify_search;

  /**
   * SearchController constructor.
   * @param SpotifySearchService $spotify_search
   */
  public function __construct(SpotifySearchService $spotify_search)
  {
    $this->spotify_search = $spotify_search;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('spotify_search.search')
    );
  }

  public function search_results() {
    return [
      "#markup" => $this->spotify_search->_spotify_api_get_query('Queen'),
    ];
  }
}
