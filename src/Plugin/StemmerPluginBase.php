<?php

namespace Drupal\porterstemmer\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Stemmer plugins.
 */
abstract class StemmerPluginBase extends PluginBase implements StemmerPluginInterface {

  /**
   * Main function for returning a block of stemmed text.
   *
   * @return string
   *   A stemmed version of the original text string.
   */
  public function stemText() {
    return $text;
  }

}
