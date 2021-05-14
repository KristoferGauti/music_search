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
    $discogs_data = $this->discogs_service->get_data();
    $data = json_decode($this->spotify_service->get_data());
    $results = []; //this gets all info thats neccessary from the users selection.
//    if ($radio_value == 'artist') {
//      foreach($checkbox_values as $index) {
//        if(is_string($index)) {
//          $artist_data = $data->artists->items[$index];
//          $artist_array = [];
//          $artist_name = $artist_data->name;
//
//          array_push($artist_array, $artist_name);
//          //For genre edge case, if genre is null.
//          if($artist_data->genres) {
//            $artist_genre = $artist_data->genres[0];
//            array_push($artist_array, $artist_genre);
//          } elseif($artist_data->images){ //Edge case where thumbnail does not exist.
//            $artist_image = $artist_data->images[0]->url;
//            $str_image = '<img class = "stuff" src=' . $artist_image . ' width = "200" >';
//            array_push($artist_array,$str_image);
//          }
//          array_push($results, $artist_array);
//        }
//      }
//    }

    if ($radio_value == "artist") {
      $new_data = $data->artists->items;
      foreach($checkbox_values as $index) {
        $temp = [];
        if (is_string($index)) {
          $data_list = $new_data[$index];
          if (sizeof($data_list->genres) != 0 or sizeof($data_list->images) != 0) {
            array_push($temp, $data_list->name, $data_list->id, '<img src=' . $data_list->images[0]->url . ' width = "200" >' , $data_list->genres[0]);
          }
          else {
            array_push($temp, $data_list->name, $data_list->id);
          }
          array_push($results,$temp);
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

    //$discogs_data->:
    //              ->title
    //              ->thumb
    //              ->id
    foreach($checkbox_values as $index) {
      $album_arr = [];
      if(is_string($index)){
        $disc = $discogs_data[intval($index)];
        $id = $disc->id;
        $title =$disc->title;
        $thumb =$disc->thumb;
        $thumb_html = '<img class = "stuff" src=' . $thumb . ' width = "200" >';
        array_push($album_arr, $thumb_html,$title,$id);
        array_push($results,$album_arr);
      }else {
        break;
      }
    }

    $this->config("music_search.search_results")
      ->set("result_list", $results)
      ->save();






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
}
