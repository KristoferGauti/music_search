<?php
namespace Drupal\music_search\Form;

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
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $checkbox_values = $this->config("music_search.search_results")->get("checkbox_values");


    $form['name'] = array(
      '#type' => 'checkboxes',
      '#options' => $checkbox_values,
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
