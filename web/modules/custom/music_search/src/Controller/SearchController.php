<?php
namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SearchController
 *
 * @package Drupal\music_search\Controller
 */
class SearchController extends ControllerBase {
  public function search_results() {
    return [
      "#markup" => $this->t("display the results from the search on this page!")
    ];
  }
}
