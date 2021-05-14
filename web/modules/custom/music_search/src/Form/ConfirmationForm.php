<?php
namespace Drupal\music_search\Form;

use DOMDocument;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\music_search\DiscogsSearchService;
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
      $container->get('discogs_search.search')
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
    $discogs_data = $this->discogs_service->get_data();
    $results_spotify = [];
    $results_discogs = [];

    if ($radio_value == "artist") {
      $new_data = $data->artists->items;
      foreach($checkbox_values as $index) {
        $temp = [];
        if (is_string($index)) {
          $item = $new_data[$index];
          $name = "<strong>Artist name: " . $item->name . "</strong>";
          $id = "Spotify ID: " . $item->id;
          if (sizeof($item->genres) != 0 or sizeof($item->images) != 0) {
            $thumbnail_url = $item->images[0]->url;
            $genre = "Genre: " . $item->genres[0];
            array_push($temp, $name , $id, $genre, '<img src=' . $thumbnail_url . ' width = "200" >');
          }
          else {
            array_push($temp, $name, $id);
          }
          array_push($results_spotify, $temp);
        }
        else {
          break;
        }
      }

    } elseif ($radio_value == 'album') {
      foreach ($checkbox_values as $index) {
        if(is_string($index)){
          $album_array = [];
          $album_data = $data->albums->items[$index];
          $album_image = $album_data->images[0]->url;
          $album_spotify_id = "Spotify ID: " . $album_data->id;
          $str_image = '<img class = "stuff" src=' . $album_image . ' width = "200" >';
          $album_artist = "Artist name: " . $album_data->artists[0]->name;
          $album_name = "<strong>Album name: " . $album_data->name . "</strong>";
          $album_release_date = "Album relesed date: " . $album_data->release_date;
          array_push($album_array, $album_name, $album_spotify_id, $album_artist, $album_release_date, $str_image);
          array_push($results_spotify, $album_array);
        }
        else {
          break;
        }
      }
    } else {
      foreach($checkbox_values as $index) {
        $track_array = [];
        if(is_string($index)){
          $track_data = $data->tracks->items[$index];
          $track_performer = "Artist name: " . $track_data->artists[0]->name;
          $track_name = "<strong>Track name: " . $track_data->name . "</strong>";
          $track_image_url = $track_data->album->images[0]->url;
          $str_image = '<img class = "stuff" src=' . $track_image_url . ' width = "200" >';
          $track_duration = "Track duration: " . (($track_data->duration_ms)/1000);
          array_push($track_array,  $track_name, $track_performer, $track_duration, $str_image);
          array_push($results_spotify, $track_array);
        }
        else {
          break;
        }
      }
    }

    $discogs_checkbox_values = $this->config("music_search.search_results")->get("discogs_checkbox_values");
    foreach($discogs_checkbox_values as $index) {
      $album_arr = [];
      if(is_string($index)){
        $item = $discogs_data[intval($index)];
        $id = "Discogs ID:" . $item->id;
        $title = "<strong>Title: " . $item->title . "</strong>";
        $thumb = $item->thumb;
        $thumb_html = '<img class = "stuff" src=' . $thumb . ' width = "200" >';
        array_push($album_arr, $title, $id, $thumb_html);
        array_push($results_discogs, $album_arr);
      }else {
        break;
      }
    }

    //fix this for discogs
    $this->config("music_search.search_results")
      ->set("result_list", $results_spotify)
      ->save();


    $form['name'] = array(
      '#type' => 'checkboxes',
      "#title" => "Spotify Data",
      '#options' => $this->_format_into_a_option_list($results_spotify),
    );

    $form['Discogs_name'] = array(
      '#type' => 'checkboxes',
      "#title" => "Discogs Data",
      '#options' => $this->_format_into_a_option_list($results_discogs),
    );


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


//    $values = $node->get('field_multiple')->getValue();
//    $values[] = ['value' => 'extra value'];
//    $node->set('field_multiple', $values);
    // for me learning






    $data = $this->config('music_search.search_results')->get("result_list");
    $radio_value = $this->config("music_search.search")->get("rad_val");
//----------------------------Beginning of the end-----------------
//    if ($radio_value == 'artist') {
//      $type = \Drupal::entityTypeManager()->getStorage('node_type')->load('artist');
//      $values = array(
//
//      );
//    } elseif ($radio_value == 'album') {
//      $type = \Drupal::entityTypeManager()->getStorage('node_type')->load('album');
//      $values = array(
//        'field_artist_reference' => [
//          ['target_id' => $]
//        ]
//      );
//
//    } else { //this is: track
//      $type = \Drupal::entityTypeManager()->getStorage('node_type')->load('songs');
//---------------------------------Helper material--------------
//    }
//    $values = array(
//      'type' => 'players',
//      'uid' => $user->uid,
//      'status' => 1,
//      'promote' => 0,
//    );
//    $entity = entity_create('node', $values);
//
//// Then create an entity_metadata_wrapper around the new entity.
//    $wrapper = entity_metadata_wrapper('node', $entity);
//
//// Now assign values through the wrapper.
//    $wrapper->title->set($email_address);
//    $wrapper->field_first_name->set($first_name);
//// ...
//
//// Finally save the node.
//    $wrapper->save();
//----------------------------------Helper material ends------------
    parent::submitForm($form, $form_state);
  }

  private function _format_into_a_option_list($arr) {
    $options_list = [];
    foreach($arr as $result) {
      //array_push($stuff,"<hr/>");
      foreach($result as $elem) {
        array_push($options_list, $elem);
      }
    }
    return $options_list;
  }
}
