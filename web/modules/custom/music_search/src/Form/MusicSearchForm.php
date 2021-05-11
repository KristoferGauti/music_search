<?php
namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\music_search\SpotifySearchService;
use http\Env\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MusicSearchForm
 *
 * @package Drupal\music_search\Form
 */
class MusicSearchForm extends FormBase {

  /**
   * @var \Drupal\music_search\SpotifySearchService
   */
  protected $spotify_search_service;

  /**
   * MusicSearchForm constructor.
   *
   * @param \Drupal\music_search\SpotifySearchService $spotify_search_service
   */
  public function __construct(SpotifySearchService $spotify_search_service) {
    $this->spotify_search_service = $spotify_search_service;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ["music_search.search"];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return "music_search_form";
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form["Album"] = [
      "#type" => "textfield",
      "#title" => $this->t("Album"),
      "#description" => $this->t("Please provide the album name that you want to search for!"),
    ];
    $form["Artist"] = [
      "#type" => "textfield",
      "#title" => $this->t("Artist"),
      "#description" => $this->t("Please provide the artist name that you want to search for!"),
    ];
    $form["Search"] = [
      "#type" => "submit",
      "#value" => "Search"
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get("music_search.search")
    );
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $artist_name_input = $form_state->getUserInput()["Artist"];
    $song_name_input = $form_state->getUserInput()["Album"];
    $query_string = "https://api.spotify.com/v1/search?q=artist:" . $artist_name_input . "%20album:" . $song_name_input . "&type=album";
    $response = $this->spotify_search_service->_spotify_api_get_query($query_string);

    //$this->displayAlbums(render($response));
    $form_state->setRedirectUrl(Url::fromUri('internal:/search_results'));

    \Drupal::messenger()->addMessage(strval($this->displayAlbums($response)));
    //render($response);



  }




  public function displayAlbums($response) {
    $albums = json_decode($response)->{'albums'}->{'items'};
    $process_item = function($item) {
      return [
        "#theme" => 'item_list',
        '#items' => [
          'name' => $item->{'name'},
          'image' => $item->{'images'}[0]->{'url'},
          'spotify_id' => $item->{'id'}
        ]
      ];
    };

    return [
      '#theme' => 'item_list',
      '#items' => array_map($process_item, $albums),
    ];
  }
}
