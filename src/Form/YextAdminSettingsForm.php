<?php

namespace Drupal\yext\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure Yext settings for this site.
 */
class YextAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yext_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['yext.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('yext.settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];

    $form['general']['yext_api_key'] = [
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Enter the API Key from the "Answers -> Experiences" tab in your Yext dashboard.'),
      '#maxlength' => 32,
      '#required' => TRUE,
      '#title' => $this->t('API Key'),
      '#type' => 'textfield',
    ];

    $form['general']['yext_experience_key'] = [
      '#default_value' => $config->get('experience_key'),
      '#description' => $this->t('Enter the Experience Key from the "Answers -> Experiences" tab in your Yext dashboard.'),
      '#required' => TRUE,
      '#title' => $this->t('Experience key'),
      '#type' => 'textfield',
    ];

    $form['general']['yext_experience_version'] = [
      '#default_value' => $config->get('experience_version'),
      '#description' => $this->t('Enter the verion of your Yext Answers Experience (i.e. "STAGING", "PRODUCTION").'),
      '#title' => $this->t('Experience version'),
      '#type' => 'textfield',
    ];

    $form['general']['locale'] = [
      '#default_value' => $config->get('locale'),
      '#description' => $this->t('Enter the locale code for the language of your Answers Experience (i.e. en).'),
      '#title' => $this->t('Locale'),
      '#type' => 'textfield',
    ];

    $form['general']['yext_account_id'] = [
      '#default_value' => $config->get('account_id'),
      '#description' => $this->t('Enter your Yext Account ID.'),
      '#title' => $this->t('Account Id'),
      '#type' => 'textfield',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('yext.settings');
    $config
      ->set('api_key', $form_state->getValue('yext_api_key'))
      ->set('experience_key', $form_state->getValue('yext_experience_key'))
      ->set('experience_version', $form_state->getValue('yext_experience_version'))
      ->set('locale', $form_state->getValue('locale'))
      ->set('account_id', $form_state->getValue('yext_account_id'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $api_key = $form_state->getValue('yext_api_key');
    if (strlen($api_key) != 32 || !ctype_alnum($api_key)) {
      $form_state->setErrorByName('yext_api_key', $this->t("The API Key must be 32 characters in length and all alphanumberic."));
    }

    $account_id = $form_state->getValue('yext_account_id');
    if (!is_numeric($account_id)) {
      $form_state->setErrorByName('yext_account_id', $this->t("The entered Yext Account ID is invalid."));
    }

    $locale = $form_state->getValue('locale');
    $locale_regex = "/^[a-z]{2}(?:_[A-Z]{2})?$/";
    if (!preg_match($locale_regex, $locale)) {
      $form_state->setErrorByName('locale', $this->t("The entered locale is invalid."));
    }

  }


}
