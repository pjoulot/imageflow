<?php

namespace Drupal\imageflow\Plugin\ImageToolkit;

use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\ImageToolkit\ImageToolkitBase;
use Drupal\Core\ImageToolkit\ImageToolkitOperationManagerInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Imageflow toolkit for image manipulation within Drupal.
 *
 * @ImageToolkit(
 *   id = "imageflow",
 *   title = @Translation("Imageflow image manipulation toolkit")
 * )
 */
class ImageflowToolkit extends ImageToolkitBase{
  
  /**
   * The ImageMagick execution manager service.
   *
   * @var \Drupal\imagemagick\ImagemagickExecManagerInterface
   */
  protected $execManager;
  
  /**
   * The ImageResizer service.
   *
   * @var \Drupal\imageresizer\Services\ImageResizerService
   */
  protected $imageResizer;

  /**
   * The width of the image.
   *
   * @var int
   */
  protected $width;
  
  /**
   * The height of the image.
   *
   * @var int
   */
  protected $height;

  /**
   * The execution arguments.
   *
   * @var string
   */
  protected $arguments;
  
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ImageToolkitOperationManagerInterface $operation_manager, LoggerInterface $logger, ConfigFactoryInterface $config_factory, $imageresizer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $operation_manager, $logger, $config_factory);
    $this->imageResizer = $imageresizer;
    //$this->arguments = new ImagemagickExecArguments($this->execManager);
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('image.toolkit.operation.manager'),
      $container->get('logger.channel.image'),
      $container->get('config.factory'),
      $container->get('imageresizer.converter')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['jpeg'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('JPEG specific settings'),
    );

    $form['jpeg']['image_jpeg_quality'] = [
        '#type' => 'number',
        '#title' => $this->t('Quality'),
        '#description' => $this->t('Define the image quality for JPEG manipulations. Ranges from 0 to 100. Higher values mean better image quality but bigger files.'),
        '#min' => 0,
        '#max' => 100,
        '#default_value' => $this->configFactory->get('imageflow.config')->get('jpeg_quality'),
        '#field_suffix' => $this->t('%'),
    ];

    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    
    // Flush image styles
    $styles = ImageStyle::loadMultiple();
    
    /** @var ImageStyle $style */
    foreach ($styles as $style) {
      $style->flush();
    }
    
    $this->configFactory->getEditable('imageflow.config')
    ->set('jpeg_quality', $form_state->getValue(['imageflow', 'jpeg', 'image_jpeg_quality']))
    ->save();
  }
  
  /**
   * {@inheritdoc}
   */
  public function isValid() {
    // @TODO Implement valid logic.
    return TRUE;
  }
  
  /**
   * {@inheritdoc}
   */
  public function save($destination) {
    kint($destination);
    die();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * Sets image width.
   *
   * @param int $width
   *   The image width.
   *
   * @return $this
   */
  public function setWidth($width) {
    $this->width = $width;
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * Sets image height.
   *
   * @param int $height
   *   The image height.
   *
   * @return $this
   */
  public function setHeight($height) {
    $this->height = $height;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMimeType() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function isAvailable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSupportedExtensions() {
    return [
      IMAGETYPE_PNG,
      IMAGETYPE_JPEG,
      IMAGETYPE_GIF,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function parseFile() {
    $this->setWidth(100);
    $this->setHeight(100);
    return TRUE;
  }
}