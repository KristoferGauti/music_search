<?php
namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class MusicSearchForm extends FormBase {

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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl(Url::fromUri('internal:/search_results'));
  }

}
