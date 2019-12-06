<?php

namespace Drupal\media_entity_slideshow\Tests;

use Drupal\Core\Language\Language;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\simpletest\WebTestBase;

/**
 * Tests for media entity slideshow.
 *
 * @group media_entity_slideshow
 */
class MediaEntitySlideshowTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'media_entity_slideshow_test',
    'node',
  ];

  /**
   * The slideshow media bundle.
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  protected $slideshowMediaBundle;

  /**
   * The image media bundle.
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  protected $imageMediaBundle;

  /**
   * A collection of media entities, to be used in our test.
   *
   * @var \Drupal\media\MediaInterface[]
   */
  protected $mediaImageCollection;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $bundle_storage = $this->container->get('entity_type.manager')->getStorage('media_type');
    $this->slideshowMediaBundle = $bundle_storage->load('slideshow_bundle');
    $this->imageMediaBundle = $bundle_storage->load('image_bundle');
    $adminUser = $this->drupalCreateUser([
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
    ]);
    $this->drupalLogin($adminUser);

    $this->mediaImageCollection = $this->createMediaImageCollection();
  }

  /**
   * Tests media entity slideshow.
   */
  public function testMediaEntitySlideshow() {

    // If we have a bundle already the schema is correct.
    $this->assertTrue((bool) $this->slideshowMediaBundle, 'The media bundle from default configuration has been created in the database.');

    // Test the creation of a media entity of the slidehsow bundle.
    $this->drupalGet('media/add/' . $this->slideshowMediaBundle->id());
    $edit = [
      'name[0][value]' => 'My first slideshow',
      'field_slides[0][target_id]' => $this->mediaImageCollection[0]->label() . ' (' . $this->mediaImageCollection[0]->id() . ')',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('Slideshow bundle My first slideshow has been created', 'Slideshow media entity was correctly created.');
    $slideshow_id = $this->container->get('entity.query')
      ->get('media')
      ->condition('bundle', 'slideshow_bundle')
      ->sort('created', 'DESC')
      ->execute();
    $slideshow = $this->loadMedia(reset($slideshow_id));

    // Add one more slide to it.
    $this->drupalGet('media/' . $slideshow->id() . '/edit');
    $edit = [
      'field_slides[0][target_id]' => $this->mediaImageCollection[0]->label() . ' (' . $this->mediaImageCollection[0]->id() . ')',
      'field_slides[1][target_id]' => $this->mediaImageCollection[1]->label() . ' (' . $this->mediaImageCollection[1]->id() . ')',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200, 'Form submitted correctly');
    $slideshow = $this->loadMedia($slideshow->id());
    $this->assertEqual($slideshow->field_slides->count(), 2, 'A new slide was correctly added to the slideshow.');

    // Test removing one of the slides.
    $this->drupalGet('media/' . $slideshow->id() . '/edit');
    $edit = [
      'field_slides[0][target_id]' => $this->mediaImageCollection[0]->label() . ' (' . $this->mediaImageCollection[0]->id() . ')',
      'field_slides[1][target_id]' => '',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200, 'Form submitted correctly');
    $slideshow = $this->loadMedia($slideshow->id());
    $this->assertEqual($slideshow->field_slides->count(), 1, 'The deletion of one slide worked properly.');

    // Delete the slideshow entirely.
    $this->drupalGet('/media/' . $slideshow->id() . '/delete');
    $this->drupalPostForm(NULL, [], t('Delete'));
    $this->assertResponse(200, 'Form submitted correctly');
    $this->assertText('The media My first slideshow has been deleted', 'The slideshow was correctly deleted.');
  }

  /**
   * Creates an array of media images to be used in testing.
   *
   * @param int $count
   *   (optional) The number of items to create. Defaults to 3.
   *
   * @return \Drupal\media\MediaInterface[]
   *   An indexed array of fully-loaded media objects of bundle image.
   */
  private function createMediaImageCollection($count = 3) {
    $collection = [];
    for ($i = 1; $i <= $count; $i++) {
      $media = Media::create([
        'bundle' => $this->imageMediaBundle->id(),
        'name' => 'Image media ' . $i,
        'uid' => '1',
        'langcode' => Language::LANGCODE_DEFAULT,
        'status' => TRUE,
      ]);
      $image = $this->getTestFile('image');
      $media->field_imagefield->target_id = $image->id();
      $media->save();
      $collection[] = $media;
    }
    return $collection;
  }

  /**
   * Load the specified media from the storage.
   *
   * @param int $id
   *   The media identifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The loaded media entity.
   */
  protected function loadMedia($id) {
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('media');
    return $storage->loadUnchanged($id);
  }

  /**
   * Retrieves a sample file of the specified type.
   *
   * @return \Drupal\file\FileInterface
   *   A file object recently created and saved.
   */
  protected function getTestFile($type_name, $size = NULL) {
    $file = current($this->drupalGetTestFiles($type_name, $size));
    $file->filesize = filesize($file->uri);
    /** @var \Drupal\file\FileInterface $file */
    $file = File::create((array) $file);
    $file->setPermanent();
    $file->save();
    return $file;
  }

}
