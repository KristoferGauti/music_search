<?php
namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class SearchController
 *
 * @package Drupal\music_search\Controller
 */
class SearchController extends ControllerBase {

  public function search_results() {
    return [
      "#markup" => t("Hello this is an unnecessary site")
    ];
  }
}
