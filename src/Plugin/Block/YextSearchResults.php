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
    $tag = '<div id="answers-container"></div><script src="' . $this->configuration['yext_search_results'] . '/iframe.js"></script>';

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
      '#default_value' => $this->configuration['yext_answers_results'],
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
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'yext_search_results' => NULL,
    ];
  }

}
