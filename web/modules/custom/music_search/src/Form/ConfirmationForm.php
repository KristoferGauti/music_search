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

/**
 * Class MusicSearchForm
 *
 * @package Drupal\music_search\Form
 */
class ConfirmationForm extends ConfigFormBase
{

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
    //$data = json_decode($this->spotify_service->get_data());

    $checkbox_values = $this->config("music_search.search_results")->get("checkbox_values");
    $all_items_html_tags = $this->config('music_search.search_results')->get('all_items');
    $stuff_to_show =[];
    foreach($checkbox_values as $value) {
      if(is_string($value) and $value != "null") {
        array_push($stuff_to_show, $all_items_html_tags[intval($value)]);

      }
    }
    //<div><p> Name: goosebumps</p><p> Spotify ID: 6gBFPUFcJLzWGx4lenP6h2</p><img src=https://i.scdn.co/image/ab67616d0000b273f54b99bf27cda88f4a7403ce width = "400" ></div>



    $form['name'] = array(
      '#type' => 'checkboxes',
      '#options' => $stuff_to_show,
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
    parent::submitForm($form, $form_state);
  }
}
