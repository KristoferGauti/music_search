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
    $pure_data_spotify =[];
    $pure_data_discogs = [];

    $associative_array_of_spotify_data = array();
    $associative_array_of_discogs_data = array();

    if ($radio_value == "artist") {
      $new_data = $data->artists->items;
      foreach($checkbox_values as $index) {
        $temp = [];
        $pure_mini = [];
        if (is_string($index)) {
          $item = $new_data[$index];
          $name =  $item->name;
          $id =  $item->id;
          $associative_array_of_spotify_data["item"] = $new_data[$index];
          $associative_array_of_spotify_data["name"] = $item->name;
          $associative_array_of_spotify_data["item"] = $item->id;
          if (sizeof($item->genres) != 0 or sizeof($item->images) != 0) {
            $thumbnail_url = $item->images[0]->url;
            $genre =  $item->genres[0];
            array_push($temp, "<strong>Artist name: " .$name. "</strong>" , "Spotify ID: " .$id, "Genre: " .$genre, '<img src=' . $thumbnail_url . ' width = "200" >');
            array_push($pure_mini,$name , $id, $genre, $thumbnail_url);
            $associative_array_of_spotify_data["thumb"] = $item->images[0]->url;
            $associative_array_of_spotify_data["genre"] = $item->genres[0];
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

    } elseif ($radio_value == 'album') {
      foreach ($checkbox_values as $index) {
        if(is_string($index)){
          $album_array = [];
          $pure_mini = [];
          $album_data = $data->albums->items[$index];
          $album_image = $album_data->images[0]->url;
          $album_spotify_id =  $album_data->id;
          $str_image = '<img class = "stuff" src=' . $album_image . ' width = "200" >';
          $album_artist =  $album_data->artists[0]->name;
          $album_name =  $album_data->name;
          $album_release_date = $album_data->release_date;

          $associative_array_of_spotify_data["album_data"] = $data->albums->items[$index];
          $associative_array_of_spotify_data["album_image"] = $album_data->images[0]->url;
          $associative_array_of_spotify_data["album_spotify_id"] = $album_data->id;
          $associative_array_of_spotify_data["album_image"] = $album_image;
          $associative_array_of_spotify_data["album_artist"] = $album_data->artists[0]->name;
          $associative_array_of_spotify_data["album_name"] = $album_data->name;
          $associative_array_of_spotify_data["album_release_date"] = $album_data->release_date;

          array_push($album_array, "<strong>Album name: " .$album_name. "</strong>", "Spotify ID: " .$album_spotify_id, "Artist name: " .$album_artist,  "Album relesed date: " .$album_release_date, $str_image);
          //We dont need this whereas we have associative arrays!!!! refactor later
          array_push($pure_mini, $album_image,$album_name,$album_artist,$album_image,$album_spotify_id);
          array_push($results_spotify, $album_array);
          array_push($pure_data_spotify, $pure_mini);
        }
        else {
          break;
        }
      }
    } else {
      foreach($checkbox_values as $index) {
        $track_array = [];
        $pure_mini = [];
        if(is_string($index)){
          $track_data = $data->tracks->items[$index];
          $track_performer = $track_data->artists[0]->name;
          $track_name = $track_data->name;
          $track_image_url = $track_data->album->images[0]->url;
          $str_image = '<img class = "stuff" src=' . $track_image_url . ' width = "200" >';
          $track_duration = (($track_data->duration_ms)/1000);


          $associative_array_of_spotify_data["track_performer"] = $track_data->artists[0]->name;;
          $associative_array_of_spotify_data["track_name "] = $track_data->name;;
          $associative_array_of_spotify_data["track_image_url"] = $track_data->album->images[0]->url;;
          $associative_array_of_spotify_data["track_duration"] = (($track_data->duration_ms)/1000);
          $associative_array_of_spotify_data["track_spotify_id"] = $track_data->id;

          array_push($track_array,  "<strong>Track name: " .$track_name. "</strong>", "Artist name: " . $track_performer, "Track duration: " . $track_duration, $str_image);
          //We dont need this whereas we have associative arrays!!!! refactor later
          array_push($pure_mini,$track_name,$track_performer,$track_duration,$track_image_url);
          array_push($results_spotify, $track_array);
          array_push($pure_data_spotify, $pure_mini);
        }
        else {
          break;
        }
      }
    }

    $discogs_checkbox_values = $this->config("music_search.search_results")->get("discogs_checkbox_values");
    foreach($discogs_checkbox_values as $index) {
      $album_arr = [];
      $pure_mini = [];
      if(is_string($index)){
        $item = $discogs_data[intval($index)];
        $id =  $item->id;
        $title =  $item->title ;
        $thumb = $item->thumb;
        $thumb_html = '<img class = "stuff" src=' . $thumb . ' width = "200" >';


        $associative_array_of_discogs_data["discogs_id"] =  $item->id;
        $associative_array_of_discogs_data["title"] = $item->title;
        $associative_array_of_discogs_data["thumbnail"] = $item->thumb;

        array_push($album_arr, "<strong>Title: " .$title. "</strong>", "Discogs ID:" .$id, $thumb_html);
        //We dont need this whereas we have associative arrays!!!! refactor later
        array_push($pure_mini, $title, $id, $thumb);
        array_push($results_discogs, $album_arr);
        array_push($pure_data_discogs, $pure_mini);
      }else {
        break;
      }
    }
    //fix this for discogs
    $this->config("music_search.search_results")
      ->set("spotify_pure",$pure_data_spotify)
      ->set("discogs_pure",$pure_data_discogs)
      ->set("spotify_pure_associative", $associative_array_of_spotify_data)
      ->set("discogs_pure_associative", $associative_array_of_discogs_data)
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
    //We dont need this whereas we have associative arrays!!!! refactor later
//    $spotify_data = $this->config('music_search.search_results')->get("spotify_pure");
//    $discogs_data = $this->config('music_search.search_results')->get("discogs_pure");
    $checkbox_values_spotify = $form_state->getValue('name');
    $checkbox_values_discogs = $form_state->getValue('Discogs_name');

//    $spotify_data_chosen = $this->_get_selected_data($spotify_data,$checkbox_values_spotify);
//    $discogs_data_chosen = $this->_get_selected_data($discogs_data,$checkbox_values_discogs);
    $radio_value = $this->config("music_search.search")->get("rad_val");

    $spotify_data = $this->config('music_search.search_results')->get("spotify_pure_associative");
    $discogs_data = $this->config('music_search.search_results')->get("discogs_pure_associative");

    //$this->create_data($radio_value, $spotify_data_chosen);
    $this->create_data($radio_value, $spotify_data);
    //$this->create_data($radio_value, $discogs_data_chosen);




      parent::submitForm($form, $form_state);
  }
  public function create_data($type, $data) {
    $store = \Drupal::entityTypeManager()->getStorage('node');
    $vals['type'] = $type;
    $vals['status'] = 1;
    if ($type == 'artist') {
      // Set values for new artist
    } elseif ($type == 'album') {
      //$vals['field_thumbnail'] = $data[0];
      $vals['title'] = $data["track_name"];
      $vals['id'] = $data["track_spotify_id"];
      //$image = file_get_contents($data[0]); // string
      //$file = file_save_data($image, 'public://druplicon.png',FILE_EXISTS_REPLACE);
      //$vals['field_thumbnail'] = $file;
      //$vals['field_published_date'] = $data[''];

    } else { //this is: track
      // Set values for new song
    }
    $node = $store->create($vals);
    $node->save();
    return $node;
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

  private function _get_selected_data($data, $checkbox_data) {
    $single_list = [];
    $all_data = [];

    foreach($data as $item){
      foreach($item as $attribute) {
        array_push($single_list, $attribute);
      }
    }

    foreach ($checkbox_data as $index) {
      if (is_string($index)) {
        array_push($all_data, $single_list[intval($index)]);
      }

    }
    return $all_data;
  }
}
