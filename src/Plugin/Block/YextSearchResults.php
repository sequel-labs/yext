<?php

namespace Drupal\yext\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Yext Search Results' block.
 *
 * @Block(
 *   id = "yextsearchresults_block",
 *   admin_label = @Translation("Yext Search Results"),
 *
 * )
 */
class YextSearchResults extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // data-path attribute allows us to set the vertical, the attribute shouldn't be present when there is no vertical
    $path = $this->configuration['yext_search_path'] ? ' data-path="' . $this->configuration['yext_search_path'] . '"' : '';
    $tag = '<div id="answers-container"' . $path . '></div><script src="' . $this->configuration['yext_search_results'] . '/iframe.js"></script>';

    return [
      '#markup' => $tag,
      '#allowed_tags' => ['script', 'div'],
      '#attached' => [
        'library' => [
          'yext/yext',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['yext_search_results'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the url of the Pages Site that you linked to your Answers Experience on the "Answers -> Experiences" tab in your Yext dashboard.'),
      '#title' => $this->t('Yext Answers Page'),
      '#default_value' => $this->configuration['yext_search_results'],
    ];
    $form['yext_search_path'] = [
      '#type' => 'textfield',
      '#description' => $this->t('If you do not want to show the root url of your Answers Experience, enter the URL of a specific page in your experience (like a vertical page, or an international subfolder).'),
      '#title' => $this->t('Yext Answers Path'),
      '#default_value' => $this->configuration['yext_search_path'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $yext_answers_results = $form_state->getValue('yext_search_results');
    if (!filter_var($yext_answers_results, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('yext_search_results', $this->t("The entered Yext Answers Page is not a valid url."));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['yext_search_results'] = $form_state->getValue('yext_search_results');
    $this->configuration['yext_search_path'] = $form_state->getValue('yext_search_path');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'yext_search_results' => NULL,
      'yext_search_path' => NULL,
    ];
  }

}
