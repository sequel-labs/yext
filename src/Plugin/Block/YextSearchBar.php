<?php

namespace Drupal\yext\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Yext Search Bar' block.
 *
 * @Block(
 *   id = "yextsearchbar_block",
 *   admin_label = @Translation("Yext Search Bar"),
 *
 * )
 */
class YextSearchBar extends BlockBase implements ContainerFactoryPluginInterface {

  protected static $yextComponentIndex = 0;

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
    $index = self::$yextComponentIndex++;
    $id = 'yext-search-bar-'.$index;

    $html = '<div id="'.$id.'" class="search_form"></div>';

    $options = [
      'apiKey'        => $this->configuration['api_key'],
      'experienceKey' => $this->configuration['experience_key'],
      'experienceVersion' => $this->configuration['experience_version'],
      'accountId' => $this->configuration['account_id'],
      'locale' => $this->configuration['locale'],
    ];
    $searchBar = [
      'component' => [
        'container'       => "#$id",
        'name'            => $id,
        'verticalKey'     => $this->configuration['vertical_key'],
        'redirectUrl'     => $this->configuration['redirect_url'],
        'placeholderText' => $this->configuration['search_placeholder'],
      ],
      'placeholderAnimation' => !!$this->configuration['animate_placeholder'],
    ];

    return [
      '#markup' => $html,
      '#allowed_tags' => ['script', 'div'],
      '#attached' => [
        'library' => [
          'yext/yext',
          'yext/yext_searchbar',
          'yext/axios.axiosjs',
          'yext/typed.typedjs',
        ],
        'drupalSettings' => [
          'yext' => [
            'yextOptions' => $options,
            'yextSearchBars' => [
              $index => $searchBar,
            ]
          ]
        ]
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['api_key'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the API Key from the "Answers -> Experiences" tab in your Yext dashboard.'),
      '#title' => $this->t('API Key'),
      '#default_value' => $this->configuration['api_key'],
    ];

    $form['experience_key'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the Experience Key from the "Answers -> Experiences" tab in your Yext dashboard.'),
      '#title' => $this->t('Experience Key'),
      '#default_value' => $this->configuration['experience_key'],
    ];

    $form['vertical_key'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the Vertical Key from the "Answers -> Vertical" tab in your Yext dashboard.'),
      '#title' => $this->t('Vertical Key'),
      '#default_value' => $this->configuration['vertical_key'],
    ];

    $form['experience_version'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the verion of your Yext Answers Experience (i.e. "STAGING", "PRODUCTION").'),
      '#title' => $this->t('Experience version'),
      '#default_value' => $this->configuration['experience_version'],
    ];

    $form['account_id'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter your Yext Account ID.'),
      '#title' => $this->t('Account Id'),
      '#default_value' => $this->configuration['account_id'],
    ];
    $form['locale'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the locale code for the language of your Answers Experience (i.e. en).'),
      '#title' => $this->t('Locale'),
      '#default_value' => $this->configuration['locale'],
    ];
    $form['redirect_url'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the url of the page to which a search should redirect. Note: This page should contain the "Yext Answers Result" block.'),
      '#title' => $this->t('Redirect Url'),
      '#default_value' => $this->configuration['redirect_url'],
    ];
    $form['search_placeholder'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the placeholder text you would like to appear in the Yext Answers Bar.'),
      '#title' => $this->t('Search Placeholder'),
      '#default_value' => $this->configuration['search_placeholder'],
    ];
    $form['animate_placeholder'] = [
      '#type' => 'checkbox',
      '#description' => $this->t('Animate the placeholder in the Yext Answers Bar with autosuggestions.'),
      '#title' => $this->t('Animate Placeholder'),
      '#default_value' => $this->configuration['animate_placeholder'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('api_key');
    if (strlen($api_key) != 32 || !ctype_alnum($api_key)) {
      $form_state->setErrorByName('api_key', $this->t("The API Key must be 32 characters in length and all alphanumberic."));
    }

    $account_id = $form_state->getValue('account_id');
    if (!is_numeric($account_id)) {
      $form_state->setErrorByName('account_id', $this->t("The entered Yext Account ID is invalid."));
    }

    $locale = $form_state->getValue('locale');
    $locale_regex = "/^[a-z]{2}(?:_[A-Z]{2})?$/";
    if (!preg_match($locale_regex, $locale)) {
      $form_state->setErrorByName('locale', $this->t("The entered locale is invalid."));
    }

    $redirect_url = $form_state->getValue('redirect_url');
    if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('redirect_url', $this->t("The entered Redirect Url is not a valid url."));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['api_key'] = $form_state->getValue('api_key');
    $this->configuration['experience_key'] = $form_state->getValue('experience_key');
    $this->configuration['vertical_key'] = $form_state->getValue('vertical_key');
    $this->configuration['experience_version'] = $form_state->getValue('experience_version');
    $this->configuration['account_id'] = $form_state->getValue('account_id');
    $this->configuration['locale'] = $form_state->getValue('locale');
    $this->configuration['redirect_url'] = $form_state->getValue('redirect_url');
    $this->configuration['search_placeholder'] = $form_state->getValue('search_placeholder');
    $this->configuration['animate_placeholder'] = $form_state->getValue('animate_placeholder');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_key' => NULL,
      'experience_key' => NULL,
      'vertical_key' => NULL,
      'experience_version' => "PRODUCTION",
      'account_id' => NULL,
      'locale' => NULL,
      'redirect_url' => NULL,
      'search_placeholder' => NULL,
      'animate_placeholder' => FALSE,
    ];
  }

}
