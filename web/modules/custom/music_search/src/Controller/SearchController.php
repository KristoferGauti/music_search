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
  public function search_results() {


    return [
      "#markup" => $this->t("hello")
    ];
  }
}
