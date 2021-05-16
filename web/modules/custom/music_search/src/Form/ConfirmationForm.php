<?php
namespace Drupal\music_search\Form;

use DOMDocument;
use DOMXPath;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
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
    $checkbox_values_discogs = $this->config("music_search.search_results")->get("discogs_checkbox_values");
    $radio_value = $this->config("music_search.search")->get("rad_val");
    $spotify_data = json_decode($this->spotify_service->get_data());
    $discogs_data = $this->discogs_service->get_data();
    $this->_get_check_box_association_list_spotify($radio_value, $checkbox_values, $spotify_data);
    $this->_get_checkbox_values_association_list_discogs($discogs_data, $checkbox_values_discogs);

    $results_spotify = $this->config("music_search.search_results")->get("results_spotify");
    $results_discogs = $this->config("music_search.search_results")->get("results_discogs");

    $form['name'] = array(
      '#type' => 'checkboxes',
      "#title" => "Spotify Data",
      '#options' => $results_spotify,
    );

    $form['Discogs_name'] = array(
      '#type' => 'checkboxes',
      "#title" => "Discogs Data",
      '#options' => $results_discogs,
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
    $checkbox_values_spotify = $form_state->getValue('name');
    $checkbox_values_discogs = $form_state->getValue('Discogs_name');

    $radio_value = $this->config("music_search.search")->get("rad_val");

    $results_spotify = $this->config("music_search.search_results")->get("results_spotify");
    $results_discogs = $this->config("music_search.search_results")->get("results_discogs");
    $selected_data = $this->_get_data($results_spotify, $results_discogs, $checkbox_values_spotify, $checkbox_values_discogs, $radio_value);


    if ($selected_data) {
      $this->create_data($radio_value, $selected_data);
      parent::submitForm($form, $form_state);
    }
    else {
      if ($radio_value == "artist") {
        //send error message according to the data types in the artist content type
      }
      elseif ($radio_value == "album") {
        \Drupal::messenger()->addError("Error. You have to choose album name, artist name, album released date and a thumbnail, one of each!");
      }
      else {
        \Drupal::messenger()->addError("Error. You have to choose track name, track id and track duration, one of each!");
      }
    }

  }

  private function _get_data($spotify_results, $discogs_results, $spotify_checkbox_values, $discogs_checkbox_values, $radio_value) {
    $spotify_chosen_data = $this->_get_chosen_data($spotify_results, $spotify_checkbox_values);
    $discogs_chosen_data = $this->_get_chosen_data($discogs_results, $discogs_checkbox_values);
    if ($radio_value == "artist") {
      //call validate data function
      $a=0;
      return $a;
    }
    elseif ($radio_value == "album") {
      //call validate data function
      $selected_data = $this->validate_data(array_merge($spotify_chosen_data, $discogs_chosen_data), [
        "Artist name",
        "<strong>Album name",
        "Album released date",
        "<img "
      ], 4);
      return $selected_data;
    }
    else {
      $selected_data = $this->validate_data(array_merge($spotify_chosen_data, $discogs_chosen_data), [
        "<strong>Track name",
        "Track duration",
        "ID"
      ],  3);
      return $selected_data;
    }
  }

  private function _get_chosen_data($arr, $checkbox_arr) {
    $chosen_data_arr = [];
    foreach($checkbox_arr as $index) {
      if (is_string($index)) {
        array_push($chosen_data_arr, $arr[intval($index)]);
      }
    }
    return $chosen_data_arr;
  }

  public function validate_data($arr, $valid_datatypes_arr, $valid_count) {
    $valid_counter = 0;
    $no_dupl_keys_arr = [];
    $no_dupl_values_arr = [];
    $duplicate_bool = false;
    foreach($arr as $item) {
      if (strpos($item, "<img") !== false) {
        $key_value = explode("src=", $item);
      }
      else {
        $key_value = explode(":", $item);
      }
      if (in_array($key_value[0], $valid_datatypes_arr)) {
        if (!in_array($key_value[0], $no_dupl_keys_arr)) {
          array_push($no_dupl_keys_arr, $key_value[0]);
          if (strpos($item, "<img") !== false) {
            array_push($no_dupl_values_arr, ["img_tag" => $item]);
          }
          else {
            array_push($no_dupl_values_arr, [$key_value[0] => $key_value[1]]);
          }
          $valid_counter += 1;
        }
        else {
          $duplicate_bool = true;
        }
      }
    }
    if ($valid_counter == $valid_count and !$duplicate_bool) {
      $associative_arr_data = array();
      foreach($no_dupl_values_arr as $real_associative_item) {
        $associative_arr_data = array_merge($associative_arr_data, $real_associative_item);
      }
      $a = 10;
      return $associative_arr_data;
    }
    else {
      return null;
    }
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function _create_media($url) {
    $file_data = file_get_contents($url);
    //'public://test.png'
    $link_array = explode("/", $url);
    $file_hash = end($link_array);
    $filename = 'public://' . strval($file_hash) . '.png';
    $file = file_save_data($file_data, $filename, FileSystemInterface::EXISTS_REPLACE);
    $media = Media::create([
      'bundle'           => 'image',
      'uid'              => \Drupal::currentUser()->id(),
      'field_media_image' => $file->id(),
    ]);
    $media->save();
    return $media->id();
  }

  public function create_data($type, $data) {
    $store = \Drupal::entityTypeManager()->getStorage('node');
    $vals['type'] = $type;
    $vals['status'] = 1;
    if ($type == 'artist') {
      // Set values for new artist
    } elseif ($type == 'album') {
      $doc = new DOMDocument();
      $doc->loadHTML($data["img_tag"]);
      $xpath = new DOMXPath($doc);
      $url = $xpath->evaluate("string(//img/@src)");
      $file_id = $this->_create_media($url);
      $title = str_replace("</strong>", "", $data["<strong>Album name"]);
      $vals['title'] = $title;
      $vals['field_published_date'] = $data["Album released date"];
      $vals["field_thumbnail"] = [
        ['target_id' => $file_id]
      ];
      $vals["field_published_date"] = trim($data["Album released date"], " ");
    }
    else { //this is: track
      $title = str_replace("</strong>", "", $data["<strong>Track name"]);
      $minute_float = floatval(explode(".", strval($data["Track duration"]))[0]) / 60;
      $minute_str = strval(floor(floatval(explode(".", strval($data["Track duration"]))[0]) / 60));
      $seconds_string = strval(intval(floor(floatval("0." . explode(".", $minute_float)[1]) * 60)));
      $vals["type"] = "songs";
      $vals["title"] = $title;
      $vals['field_spotify_id'] = $data["ID"];
      $vals["field_duration"] = $minute_str . ":" . $seconds_string;
    }
    $node = $store->create($vals);
    $node->save();
    return $node;
  }

  private function _get_check_box_association_list_spotify($radio_value, $checkbox_values, $data) {
    $results_spotify = []; //html tag list of spotify data
    $clean_results_spotify = []; //no html tag list of spotify data
    $associative_array_of_spotify_data = array();

    if ($radio_value == "artist") {
      $new_data = $data->artists->items;
      foreach($checkbox_values as $index) {
        // Insert into clean_results_spotify array lastly!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $temp = [];
        $pure_mini = [];
        if (is_string($index)) {
          $item = $new_data[$index];
          $name =  $item->name;
          $id =  $item->id;
          //$associative_array_of_spotify_data["name"] = $item->name;
          if (sizeof($item->genres) != 0 or sizeof($item->images) != 0) {
            $thumbnail_url = $item->images[0]->url;
            $genre =  $item->genres[0];
            array_push($temp, "<strong>Artist name: " .$name. "</strong>" , "Spotify ID: " .$id, "Genre: " .$genre, "<img src=" . $thumbnail_url . " width = '200' >");
            array_push($pure_mini,$name , $id, $genre, $thumbnail_url);
//            $associative_array_of_spotify_data["thumb"] = $item->images[0]->url;
//            $associative_array_of_spotify_data["genre"] = $item->genres[0];
          }
          else {
            array_push($temp, $name, $id);
            array_push($pure_mini,$name,$id);
          }
          array_push($results_spotify, $temp);
          array_push($pure_data_spotify, $pure_mini);
        }
        else {
          break;
        }
      }
    }
    elseif ($radio_value == 'album') {
      foreach ($checkbox_values as $index) {
        if(is_string($index)){
          $album_array = [];
          $album_data = $data->albums->items[$index];
          $album_image = $album_data->images[0]->url;
          $album_spotify_id =  $album_data->id;
          $str_image = "<img src=" . $album_image . " width = '200' >";
          $album_artist =  $album_data->artists[0]->name;
          $album_name =  $album_data->name;
          $album_release_date = $album_data->release_date;
          array_push($clean_results_spotify, $album_name, $album_artist, $album_release_date, $album_image);

//          $associative_array_of_spotify_data["album_image"] = $album_data->images[0]->url;
//          $associative_array_of_spotify_data["album_spotify_id"] = $album_data->id;
//          $associative_array_of_spotify_data["album_image"] = $album_image;
//          $associative_array_of_spotify_data["album_artist"] = $album_data->artists[0]->name;
//          $associative_array_of_spotify_data["album_name"] = $album_data->name;
//          $associative_array_of_spotify_data["album_release_date"] = $album_data->release_date;

          array_push($clean_results_spotify, $album_name,$album_spotify_id, $album_artist, $album_release_date, $album_image);
          array_push($album_array, "<strong>Album name: " .$album_name. "</strong>", "Artist name: " .$album_artist,  "Album released date: " .$album_release_date, $str_image);
          $results_spotify = array_merge($results_spotify, $album_array);
        }
        else {
          break;
        }
      }
    }
    else {
      foreach($checkbox_values as $index) {
        $track_array = [];
        if(is_string($index)){
          $track_data = $data->tracks->items[$index];
          $track_name = $track_data->name;
          $spotify_id = $track_data->id;
          $track_duration = (($track_data->duration_ms)/1000);
          array_push($clean_results_spotify, $track_name, $spotify_id, $track_duration);
          array_push($track_array,  "<strong>Track name: " .$track_name. "</strong>", "ID: " . $spotify_id  , "Track duration: " . $track_duration);
          $results_spotify = array_merge($results_spotify, $track_array);
        }
        else {
          break;
        }
      }
    }

    $this->config("music_search.search_results")
      ->set("results_spotify", $results_spotify)
      ->set("clean_results_spotify", $clean_results_spotify)
      ->save();
  }

  private function _get_checkbox_values_association_list_discogs($discogs_data, $discogs_checkbox_values) {
    //$associative_array_of_discogs_data = array();
    $radio_value = $this->config("music_search.search")->get("rad_val");
    $results_discogs = [];
    $clean_results_discogs = [];
    foreach($discogs_checkbox_values as $index) {
      $album_arr = [];
      if(is_string($index)){
        $item = $discogs_data[intval($index)];
        $id =  $item->id;
        $title =  $item->title ;
        $thumb = $item->thumb;
        $thumb_html = "<img src=" . $thumb . " width = '200' >";
        array_push($clean_results_discogs, $title, $id, $thumb);

//        $associative_array_of_discogs_data["discogs_id"] =  $item->id;
//        $associative_array_of_discogs_data["title"] = $item->title;
//        $associative_array_of_discogs_data["thumbnail"] = $item->thumb;

        if ($radio_value == "track") {
          array_push($album_arr, "<strong>Track name: " .$title. "</strong>", "ID:" .$id);
        }
        elseif ($radio_value == "album") {
          array_push($album_arr, "<strong>Album name: " .$title. "</strong>", $thumb_html);
        }
        else {
          array_push($album_arr, "<strong>Track name: " .$title. "</strong>", "ID:" .$id, $thumb_html);
        }
        $results_discogs = array_merge($results_discogs, $album_arr);
      }else {
        break;
      }
    }
    $this->config("music_search.search_results")
      ->set("results_discogs", $results_discogs)
      ->set("clean_results_discogs", $clean_results_discogs)
      ->save();
  }
}
