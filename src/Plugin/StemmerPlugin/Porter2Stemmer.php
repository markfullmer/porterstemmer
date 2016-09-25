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
   * {@inheritdoc}
   */
  public function __construct($text) {
    // Convert text to lower case, and replace special apostrophes with regular
    // apostrophes.
    $text = strtolower(str_replace('’', "'", $text));

    // Split into words.
    $this->words = preg_split('/[^a-zA-Z\']+/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $this->text = $text;
  }

  /**
   * Main function for returning a block of stemmed text.
   *
   * @return string
   *   A stemmed version of the original text string.
   */
  public function stemText() {
    if (empty($this->words)) {
      return $this->text;
    }
    // Process each word.
    foreach ($this->words as $k => $word) {
      $words[$k] = $this->stem($word);
    }
    // Put it all back together.
    return implode(' ', $words);
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
    $stemmer = new porter2($word);
    return $stemmer->stem();
  }

}

/**
 * PHP Implementation of the Porter2 Stemming Algorithm.
 *
 * See http://snowball.tartarus.org/algorithms/english/stemmer.html .
 */
class porter2 {

  private $word = '';

  /**
   * The R1 of the word: http://snowball.tartarus.org/texts/r1r2.html.
   */
  private $r1;

  /**
   * The R2 of the word: http://snowball.tartarus.org/texts/r1r2.html.
   */
  private $r2;

  /**
   * Allow a key => value array of exceptions to be passed to exceptions().
   */
  public $custom_exceptions = array();

  /**
   * Prepare the word form for processing.
   */
  public function __construct($word) {
    $this->word = $word;
    // Remove initial ', if present.
    if ($this->length() > 2 && substr($this->word, 0, 1) === "'") {
      $this->word = substr($this->word, 1, $this->length() - 1);
    }
    // Set initial y, or y after a vowel, to Y.
    $inc = 0;
    while ($inc <= $this->length()) {
      if (substr($this->word, $inc, 1) === 'y' && ($inc == 0 || $this->isVowel($inc - 1))) {
        $this->word = substr_replace($this->word, 'Y', $inc, 1);

      }
      $inc++;
    }
    // Establish the regions R1 and R2. See function R().
    $this->r1 = $this->R(1);
    $this->r2 = $this->R(2);
  }

  /**
   * The only public-facing method. Call ->stem(WORD) to return stemmed word.
   */
  public function stem() {
    // Ignore exceptions & words that are two letters or less.
    if ($this->exceptions() || $this->length() <= 2) {
      return strtolower($this->word);
    }
    else {
      $this->step0();
      $this->step1a();
      $this->step1b();
      $this->step1c();
      $this->step2();
      $this->step3();
      $this->step4();
      $this->step5();
    }
    return strtolower($this->word);
  }

  /**
   * Is the word in a given list of exceptions? If so, replace and end process.
   */
  private function exceptions() {
    $checks = array(
      'skis' => 'ski',
      'skies' => 'sky',
      'dying' => 'die',
      'lying' => 'lie',
      'tying' => 'tie',
      'idly' => 'idl',
      'gently' => 'gentl',
      'ugly' => 'ugli',
      'early' => 'earli',
      'only' => 'onli',
      'singly' => 'singl',
      'sky' => 'sky',
      'news' => 'news',
      'howe' => 'howe',
      'atlas' => 'atlas',
      'cosmos' => 'cosmos',
      'bias' => 'bias',
      'andes' => 'andes',
    );
    $checks = array_merge($checks, $this->custom_exceptions);
    if (in_array($this->word, array_keys($checks))) {
      foreach ($checks as $find => $replace) {
        if ($find === $this->word) {
          $this->word = $replace;
          break;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Step 0: Search for the longest among the "s" suffixes & remove.
   */
  private function step0() {
    $found = FALSE;
    $checks = array("'s'", "'s", "'");
    foreach ($checks as $check) {
      if (!$found && $this->hasEnding($check)) {
        $this->removeEnding($check);
        $found = TRUE;
      }
    }
  }

  /**
   * Step 1a: Search for the longest among the following suffixes.
   */
  private function step1a() {
    $found = FALSE;
    if ($this->hasEnding('sses')) {
      $this->removeEnding('sses');
      $this->addEnding('ss');
      $found = TRUE;
    }
    $checks = array('ied', 'ies');
    foreach ($checks as $check) {
      if (!$found && $this->hasEnding($check)) {
        if ($this->length() > 4) {
          $this->removeEnding($check);
          $this->addEnding('i');
        }
        else {
          $this->removeEnding($check);
          $this->addEnding('ie');
        }
        $found = TRUE;
      }
    }
    if ($this->hasEnding('us') || $this->hasEnding('ss')) {
      $found = TRUE;
    }
    // Delete if preceding word part has a vowel not immediately before the s.
    if (!$found && $this->hasEnding('s') && $this->containsVowel(substr($this->word, 0, -2))) {
      $this->removeEnding('s');
    }
  }

  /**
   * Step 1b.
   */
  private function step1b() {
    $exceptions = array(
      'inning', 'outing', 'canning', 'herring', 'earring', 'proceed', 'exceed', 'succeed',
    );
    if (in_array($this->word, $exceptions)) {
      return;
    }
    $checks = array('eedly', 'eed');
    foreach ($checks as $check) {
      if ($this->hasEnding($check)) {
        if ($this->r1 !== $this->length()) {
          $this->removeEnding($check);
          $this->addEnding('ee');
        }
        return;
      }
    }
    $checks = array('ingly', 'edly', 'ing', 'ed');
    $second_endings = array('at', 'bl', 'iz');
    foreach ($checks as $check) {
      // If the ending is present and the previous part contains a vowel.
      if ($this->hasEnding($check) && $this->containsVowel(substr($this->word, 0, $this->length() - strlen($check)))) {
        $this->removeEnding($check);
        foreach ($second_endings as $ending) {
          if ($this->hasEnding($ending)) {
            $this->addEnding('e');
            return;
          }
        }
        // If the word ends with a double, remove the last letter.
        $found = $this->removeDoubles();
        // If the word is short, add e (so hop -> hope).
        if (!$found && ($this->isShort())) {
          $this->addEnding('e');
        }
        return;
      }
    }
  }

  /**
   * Step 1c. Replace suffix y or Y with i if after non-vowel not @ word begin.
   */
  private function step1c() {
    if (($this->hasEnding('y') || $this->hasEnding('Y')) && $this->length() > 2 && !($this->isVowel($this->length() - 2))) {
      $this->removeEnding('y');
      $this->removeEnding('Y');
      $this->addEnding('i');
    }
  }

  /**
   * Step 2.
   */
  private function step2() {
    $checks = array(
      "ization" => "ize",
      "iveness" => "ive",
      "fulness" => "ful",
      "ational" => "ate",
      "ousness" => "ous",
      "biliti" => "ble",
      "tional" => "tion",
      "lessli" => "less",
      "fulli" => "ful",
      "entli" => "ent",
      "ation" => "ate",
      "aliti" => "al",
      "iviti" => "ive",
      "ousli" => "ous",
      "alism" => "al",
      "abli" => "able",
      "anci" => "ance",
      "alli" => "al",
      "izer" => "ize",
      "enci" => "ence",
      "ator" => "ate",
      "bli" => "ble",
      "ogi" => "og",
    );
    foreach ($checks as $find => $replace) {
      if ($this->hasEnding($find)) {
        if ($this->inR1($find)) {
          $this->removeEnding($find);
          $this->addEnding($replace);
        }
        return;
      }
    }
    if ($this->hasEnding('li')) {
      if ($this->validli(substr($this->word, -3, 1)) && $this->length() > 4) {
        $this->removeEnding('li');
      }
    }
  }

  /**
   * Step 3.
   */
  private function step3() {
    $checks = array(
      'ational' => 'ate',
      'tional' => 'tion',
      'alize' => 'al',
      'icate' => 'ic',
      'iciti' => 'ic',
      'ical' => 'ic',
      'ness' => '',
      'ful' => '',
    );
    foreach ($checks as $find => $replace) {
      if ($this->hasEnding($find)) {
        if ($this->inR1($find)) {
          $this->removeEnding($find);
          $this->addEnding($replace);
        }
        return;
      }
    }
    if ($this->hasEnding('ative')) {
      if ($this->inR2('ative')) {
        $this->removeEnding('ative');
      }
    }
  }

  /**
   * Step 4.
   */
  private function step4() {
    $checks = array(
      'ement',
      'ment',
      'ance',
      'ence',
      'able',
      'ible',
      'ant',
      'ent',
      'ion',
      'ism',
      'ate',
      'iti',
      'ous',
      'ive',
      'ize',
      'al',
      'er',
      'ic',
    );
    foreach ($checks as $check) {
      // Among the suffixes, if found and in R2, delete.
      if ($this->hasEnding($check)) {
        if ($check == 'ion') {
          if ($this->inR2('ion') && in_array(substr($this->word, -4, 1), array('s', 't'))) {
            $this->removeEnding('ion');
          }
        }
        elseif ($this->inR2($check)) {
          $this->removeEnding($check);
        }
        return;
      }
    }
  }

  /**
   * Step 5.
   */
  private function step5() {
    if ($this->hasEnding('e')) {
      // Delete if in R2, or in R1 and not preceded by a short syllable.
      if ($this->inR2('e') || ($this->inR1('e') && !$this->isShortSyllable($this->length() - 3))) {
        $this->removeEnding('e');
      }
      return;
    }
    if ($this->hasEnding('l')) {
      // Delete if in R2 and preceded by l.
      if ($this->inR2('l') && substr($this->word, -2, 1) == 'l') {
        $this->removeEnding('l');
      }
    }
  }

  /**
   * Remove doubles from the following list.
   */
  private function removeDoubles() {
    $found = FALSE;
    $doubles = array('bb', 'dd', 'ff', 'gg', 'mm', 'nn', 'pp', 'rr', 'tt');
    foreach ($doubles as $double) {
      if (substr($this->word, -2) == $double) {
        $this->word = substr($this->word, 0, $this->length() - 2) . substr($double, 1);
        $found = TRUE;
        break;
      }
    }
    return $found;
  }

  /**
   * Is the letter indicated by the string position a vowel?
   */
  private function isVowel($position, $word = NULL, $additional = array()) {
    if ($word == NULL) {
      $word = $this->word;
    }
    $vowels = array_merge(array('a', 'e', 'i', 'o', 'u', 'y'), $additional);
    if (in_array(substr($word, $position, 1), $vowels)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * From a given ending $position in a word, does it end -vowel-consonant?
   *
   * If this position is not provided, assume end of word, and
   * additionally check that the last letter is not w, x, or Y.
   */
  private function isShortSyllable($position = NULL) {
    if ($position === NULL) {
      $position = $this->length() - 2;
    }
    // A vowel at the beginning of the word followed by a non-vowel.
    if ($position === 0) {
      if ($this->isVowel(0) && !$this->isVowel(1)) {
        return TRUE;
      }
    }
    else {
      // Vowel followed by non-vowel other than w, x, Y & preceded by non-vowel.
      $additional = array('w', 'x', 'Y');
      return (!$this->isVowel($position - 1) && $this->isVowel($position) && !$this->isVowel($position + 1, NULL, $additional));
    }
  }

  /**
   * A word is called short if it ends in a short syllable and if R1 is null.
   */
  private function isShort() {
    return $this->isShortSyllable() && $this->r1 == $this->length();
  }

  /**
   * R is a region after the first non-vowel following a vowel, or end of word.
   *
   * @param int $type
   *    1 or 2. If 2, then calculate the R after the R1.
   */
  private function R($type = 1) {
    $inc = 1;
    if ($type === 2) {
      $inc = $this->r1;
    }
    else {
      if ($this->length() > 5 && (strcmp('gener', substr($this->word, 0, 5)) === 0)) {
        return 5;
      }
      if ($this->length() > 5 && (strcmp('arsen', substr($this->word, 0, 5)) === 0)) {
        return 5;
      }
      if ($this->length() > 6 && (strcmp('commun', substr($this->word, 0, 6)) === 0)) {
        return 6;
      }
    }

    while ($inc <= $this->length()) {
      if (!$this->isVowel($inc) && $this->isVowel($inc - 1)) {
        $position = $inc;
        break;
      }
      $inc++;
    }
    if (!isset($position)) {
      $position = $this->length();
    }
    else {
      // We add one, as this is the position AFTER the first non-vowel.
      $position++;
    }
    return $position;
  }

  /**
   * Is the given string in R1?
   */
  private function inR1($string = '') {
    $r1 = substr($this->word, $this->r1);
    return strpos($r1, $string) !== FALSE;
  }

  /**
   * Is the given string in R2?
   */
  private function inR2($string = '') {
    $r2 = substr($this->word, $this->r2);
    return strpos($r2, $string) !== FALSE;
  }

  /**
   * The string length of the current word.
   */
  private function length() {
    return strlen($this->word);
  }

  /**
   * Does the word end with a given string?
   */
  private function hasEnding($string) {
    $length = strlen($string);
    if ($length > $this->length()) {
      return FALSE;
    }
    return (substr_compare($this->word, $string, -1 * $length, $length) === 0);
  }

  /**
   * Append a given string to the current word state.
   */
  private function addEnding($string = '') {
    $this->word = $this->word . $string;
  }

  /**
   * Remove a given string from the current word state.
   */
  private function removeEnding($string = '') {
    $length = strlen($string);
    if (substr($this->word, -1 * $length, $length) == $string) {
      $this->word = substr($this->word, 0, $this->length() - strlen($string));
    }
  }

  /**
   * Does a given string contain a vowel?
   */
  private function containsVowel($string) {
    $inc = 0;
    $return = FALSE;
    while ($inc < strlen($string)) {
      if ($this->isVowel($inc, $string)) {
        $return = TRUE;
        break;
      }
      $inc++;
    }
    return $return;
  }

  /**
   * Is a given string in the array of valid -li prefixes?
   */
  private function validli($string = '') {
    return in_array($string, array(
      'c', 'd', 'e', 'g', 'h', 'k', 'm', 'n', 'r', 't',
    ));
  }

}
