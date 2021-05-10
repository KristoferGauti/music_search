<?php
namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\music_search\SpotifySearchService;

class MusicSearchForm extends FormBase {

//  protected $spotify_search_service;


//  public function __construct() {
//    $this->spotify_search_service = \Drupal::service("spotify_search.search");
//  }

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
//  public function submitForm(array &$form, FormStateInterface $form_state) {
//    $values = $form_state->getValue(array('nameofarray', 'value'));
//    $this->spotify_search_service->_spotify_api_get_query("");
//  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}
