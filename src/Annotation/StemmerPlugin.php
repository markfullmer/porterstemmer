<?php

namespace Drupal\porterstemmer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Stemmer plugin item annotation object.
 *
 * @see \Drupal\porterstemmer\Plugin\StemmerPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class StemmerPlugin extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The language code that the stemmer is applicable to.
   */
  public $language;

  /**
   * The controlling function for stemming the word.
   *
   * @param string $word
   *    A word to be stemmed.
   *
   * @return string
   *    The stemmed word.
   */
  public function stem($word) {
    return $word;
  }

}
