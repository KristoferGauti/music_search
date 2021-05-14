<?php
namespace Drupal\music_search\Form;

use DOMDocument;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\music_search\SpotifySearchService;
use http\Env\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function GuzzleHttp\Psr7\str;

/**
 * Class MusicSearchForm
 *
 * @package Drupal\music_search\Form
 */
class ConfirmationForm extends ConfigFormBase
{
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

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames()
  {
    return ["music_search.search_results"];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return "search_results";
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $checkbox_values = $this->config("music_search.search_results")->get("checkbox_values");
    $radio_value = $this->config("music_search.search")->get("rad_val");
    $data = json_decode($this->spotify_service->get_data());
    $results = []; //this gets all info thats neccessary from the users selection.
    if ($radio_value == 'artist') {
      foreach($checkbox_values as $index) {
        if(is_string($index)) {
          $artist_data = $data->artists->items[$index];
          $artist_array = [];
          $artist_name = $artist_data->name;

          array_push($artist_array, $artist_name);
          //For genre edge case, if genre is null.
          if($artist_data->genres) {
            $artist_genre = $artist_data->genres[0];
            array_push($artist_array, $artist_genre);
          } elseif($artist_data->images){ //Edge case where thumbnail does not exist.
            $artist_image = $artist_data->images[0]->url;
            $str_image = '<img class = "stuff" src=' . $artist_image . ' width = "200" >';
            array_push($artist_array,$str_image);
          }
          array_push($results, $artist_array);
        }
      }
    } elseif ($radio_value == 'album') {
      foreach ($checkbox_values as $index) {
        if(is_string($index)){
          $album_array = [];
          $album_data = $data->albums->items[$index];
          $album_image = $album_data->images[0]->url;
          $str_image = '<img class = "stuff" src=' . $album_image . ' width = "200" >';
          $album_artist = $album_data->artists[0]->name;
          $album_name = $album_data->name;
          $album_release_date = $album_data->release_date;
          array_push($album_array,$str_image, $album_artist,$album_name,$album_release_date);
          array_push($results,$album_array);
        }
      }
    } else { //this is: track
      foreach($checkbox_values as $index) {
        if(is_string($index)){
          $track_array = [];
          $track_data = $data->tracks->items[$index];
          $track_performer = $track_data->artists[0]->name;
          $track_name = $track_data->name;
          $track_image = $track_data->album->images[0]->url;
          $str_image = '<img class = "stuff" src=' . $track_image . ' width = "200" >';
          $track_duration = ($track_data->duration_ms)/1000;
          array_push($track_array,$str_image,$track_performer,$track_name,$track_duration);
          array_push($results,$track_array);
        }
      }
    }








    $stuff = [];
    foreach($results as $result) {
      //array_push($stuff,"<hr/>");
      foreach($result as $elem) {
        array_push($stuff, $elem);
      }
    }

    $form['name'] = array(
      '#type' => 'checkboxes',
      '#options' => $stuff,
    );

//    $form['listings'] = array(
//      '#type' => 'tableselect',
//      //'#header' => $divider,
//      '#options' => $stuff,
//      '#empty' => t('No Listing available.'),
    //);
//    $form['html'] = array(
//      '#type' => 'html',
//      '#markup' => $divider,
//    );

//
//    $form['name'] = array(
//      '#type' => 'checkboxes',
//      '#options' => $results,
//    );
    $form["Continue"] = [
      "#type" => "submit",
      "#value" => "Continue"
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);
  }
}
