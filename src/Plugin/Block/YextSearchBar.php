<?php

namespace Drupal\yext\Plugin\Block;

use Drupal\yext\Utility\RedirectUrlWidget;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
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
    $yextConfig = $this->configFactory->get('yext.settings');

    $id = $this->configuration['block_id'] ? $this->configuration['block_id'].'-search-bar' : 'yext-search-bar-' . mt_rand();

    $html = '<div id="'.$id.'" class="search_form"></div>';

    $options = [
      'apiKey'        => $yextConfig->get('api_key'),
      'experienceKey' => $yextConfig->get('experience_key'),
      'accountId' => $yextConfig->get('account_id'),
      'locale' => $yextConfig->get('locale'),
    ];

    // don't include experience_version if it has no value
    if ($yextConfig->get('experience_version')) {
      $options['experienceVersion'] = $yextConfig->get('experience_version');
    }

    $redirect_url = Url::fromUri($this->configuration['redirect_url'], ['absolute' => true])->toString();

    $searchBar = [
      'component' => [
        'container'       => "#$id",
        'name'            => $id,
        'verticalKey'     => $this->configuration['vertical_key'],
        'redirectUrl'     => $redirect_url,
        'placeholderText' => $this->configuration['search_placeholder'],
      ],
      'placeholderAnimation' => !!$this->configuration['animate_placeholder'],
    ];

    $searchIcon = $this->configuration['search_icon'] ? $this->configuration['search_icon'] : $yextConfig->get('search_icon');
    if ($searchIcon) {
      if (substr($searchIcon,0,1) === '/') {
        $searchIcon = ((substr($searchIcon,1,1) === '/') ? \Drupal::request()->getScheme().':' :  \Drupal::request()->getSchemeAndHttpHost()) . $searchIcon;
      }
      if (filter_var($searchIcon, FILTER_VALIDATE_URL)) {
        $searchBar['component']['customIconUrl'] = $searchIcon;
      } else {
        $searchBar['component']['submitIcon'] = $searchIcon;
      }
    }

    return [
      '#markup' => $html,
      '#allowed_tags' => ['script', 'div'],
      '#attached' => [
        'library' => [
          'yext/yext',
          'yext/yext_searchbar',
          'yext/typed.typedjs',
        ],
        'drupalSettings' => [
          'yext' => [
            'yextOptions' => $options,
            'yextSearchBars' => [
              $id => $searchBar,
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

    $form['vertical_key'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the Vertical Key from the "Answers -> Vertical" tab in your Yext dashboard.'),
      '#title' => $this->t('Vertical Key'),
      '#default_value' => $this->configuration['vertical_key'],
    ];
    // redirectUrl is handled like a normal link field, but without the title/label
    // to do that we hijack the link widget class and to gain access to its protected methods which transform & validate the urls
    $form['redirect_url'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#description' => $this->t('Enter the url of the page to which a search should redirect. Note: This page should contain the "Yext Answers Result" block.'),
      '#title' => $this->t('Redirect Url'),
      '#default_value' => RedirectUrlWidget::getUriAsDisplayableString(($this->configuration['redirect_url'])),
      '#process_default_value' => FALSE,
      '#element_validate' => [['\Drupal\yext\Utility\RedirectUrlWidget', 'validateUriElement']],
    ];
    $form['search_icon'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter the name or url of the icon you to show in the search bar.  If no value is specified the search icon from the Yext settings will be shown.  For more details of Yext icon names see https://hitchhikers.yext.com/docs/answers-sdk/components/search-bar/'),
      '#title' => $this->t('Search Icon'),
      '#default_value' => $this->configuration['search_icon'],
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

    // $redirect_url = $form_state->getValue('redirect_url');
    // if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
    //   $form_state->setErrorByName('redirect_url', $this->t("The entered Redirect Url is not a valid url."));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['vertical_key'] = $form_state->getValue('vertical_key');
    $this->configuration['redirect_url'] = RedirectUrlWidget::getUserEnteredStringAsUri($form_state->getValue('redirect_url'));
    $this->configuration['search_icon'] = $form_state->getValue('search_icon');
    $this->configuration['search_placeholder'] = $form_state->getValue('search_placeholder');
    $this->configuration['animate_placeholder'] = $form_state->getValue('animate_placeholder');

    $block = $form_state->getFormObject()->getEntity();
    if ($block) {
        $this->configuration['block_id'] = $block->id();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_id' => NULL,
      'vertical_key' => NULL,
      'redirect_url' => NULL,
      'search_icon' => NULL,
      'search_placeholder' => NULL,
      'animate_placeholder' => FALSE,
    ];
  }

}
