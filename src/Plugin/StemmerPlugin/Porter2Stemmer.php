<?php

namespace Drupal\porterstemmer\Plugin\StemmerPlugin;

/**
 * Stems words via Martin Porter's English stemming algorithm.
 *
 * @StemmerPlugin(
 *   id = "porter2",
 *   label = @Translation("Porter 2 Stemmer for English"),
 *   language = "en",
 *   description = @Translation("Stems words via Martin Porter's English stemming algorithm."),
 * )
 */
class Porter2Stemmer {

  /**
   * An array of words based on the stemmer's delimiters.
   */
  public $words = array();

  /**
   * The text in its original/current state.
   */
  public $text = '';

  /**
   * Definition of the stemmer's word boundaries.
   */
  public $porterstemmer_boundary = "[^a-zA-Z']+";

  /**
   * {@inheritdoc}
   */
  public function __construct($text) {
    // Convert text to lower case, and replace special apostrophes with regular
    // apostrophes.
    $text = strtolower(str_replace('’', "'", $text));

    // Split into words.
    $this->words = preg_split('/(\[^a-zA-Z\]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $this->text = $text;
  }

  /**
   * Main function for returning a block of stemmed text.
   *
   * @return string
   *   A stemmed version of the original text string.
   */
  public function stemText() {
    if (!empty($this->words)) {
      return $this->text;
    }
    // Process each word, skipping delimiters.
    $isword = !preg_match('/' . $this->$porterstemmer_boundary . '/', $words[0]);
    foreach ($this->words as $k => $word) {
      if ($isword) {
        $words[$k] = $this->stem($word);
      }
      $isword = !$isword;
    }
    // Put it all back together (note that delimiters are in $words).
    return implode('', $words);
  }

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
