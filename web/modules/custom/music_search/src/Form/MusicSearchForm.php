<?php
namespace Drupal\music_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MusicSearchForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames()
  {
    return ["music_search.search"];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return "music_search_form";
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config("music_search.search");
    $form["music_search"] = [
      "#type" => "textfield",
      "#title" => $this->t("Music Search"),
      "#description" => $this->t("Please provide the song that you want to search for!"),
      "#default value" => $config->get("salutation")
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->config("music_search.search")
      ->set("music_search", $form_state->getValue("music_search"))
      ->save();
    parent::submitForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $salutation = $form_state->getValue("music_search");
    if (strlen($salutation) > 20) {
      $form_state->setErrorByName("music_search", $this->t("This music search is too long"));
    }
    parent::validateForm($form, $form_state);
  }
}
