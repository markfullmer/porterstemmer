<?php

namespace Drupal\porterstemmer;

/**
 * PHP Implementation of the Porter2 Stemming Algorithm.
 *
 * See http://snowball.tartarus.org/algorithms/english/stemmer.html .
 */
class porter2 {

  /**
   * The word being stemmed.
   *
   * @var string
   */
  protected static $word;

  /**
   * The R1 of the word.
   *
   * @var int
   *
   * @see http://snowball.tartarus.org/texts/r1r2.html.
   */
  protected static $r1;

  /**
   * The R2 of the word.
   *
   * @var int
   *
   * @see http://snowball.tartarus.org/texts/r1r2.html.
   */
  protected static $r2;

  /**
   * List of exceptions to be used.
   *
   * @var string[]
   */
  protected static $exceptions = array();

  /**
   * Constructs a SearchApiPorter2 object.
   *
   * @param string $word
   *   The word to stem.
   */
  protected static function prepare($word) {
    self::$word = $word;
    self::$exceptions = array(
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

    // Set initial y, or y after a vowel, to Y.
    $inc = 0;
    while ($inc <= self::length()) {
      if (substr(self::$word, $inc, 1) === 'y' && ($inc == 0 || self::isVowel($inc - 1))) {
        self::$word = substr_replace(self::$word, 'Y', $inc, 1);

      }
      $inc++;
    }
    // Establish the regions R1 and R2. See function R().
    self::$r1 = self::R(1);
    self::$r2 = self::R(2);
  }

  /**
   * Computes the stem of the word.
   *
   * @return string
   *   The word's stem.
   */
  public static function stem($word) {
    self::prepare($word);
    // Ignore exceptions & words that are two letters or less.
    if (self::exceptions() || self::length() <= 2) {
      return strtolower(self::$word);
    }
    else {
      self::step0();
      self::step1a();
      self::step1b();
      self::step1c();
      self::step2();
      self::step3();
      self::step4();
      self::step5();
    }
    return strtolower(self::$word);
  }

  /**
   * Determines whether the word is contained in our list of exceptions.
   *
   * If so, the $word property is changed to the stem listed in the exceptions.
   *
   * @return bool
   *   TRUE if the word was an exception, FALSE otherwise.
   */
  protected static function exceptions() {
    if (isset(self::$exceptions[self::$word])) {
      self::$word = self::$exceptions[self::$word];
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Searches for the longest among the "s" suffixes and removes it.
   *
   * Implements step 0 of the Porter2 algorithm.
   */
  protected static function step0() {
    $found = FALSE;
    $checks = array("'s'", "'s", "'");
    foreach ($checks as $check) {
      if (!$found && self::hasEnding($check)) {
        self::removeEnding($check);
        $found = TRUE;
      }
    }
  }

  /**
   * Handles various suffixes, of which the longest is replaced.
   *
   * Implements step 1a of the Porter2 algorithm.
   */
  protected static function step1a() {
    $found = FALSE;
    if (self::hasEnding('sses')) {
      self::removeEnding('sses');
      self::addEnding('ss');
      $found = TRUE;
    }
    $checks = array('ied', 'ies');
    foreach ($checks as $check) {
      if (!$found && self::hasEnding($check)) {
        $length = self::length();
        self::removeEnding($check);
        if ($length > 4) {
          self::addEnding('i');
        }
        else {
          self::addEnding('ie');
        }
        $found = TRUE;
      }
    }
    if (self::hasEnding('us') || self::hasEnding('ss')) {
      $found = TRUE;
    }
    // Delete if preceding word part has a vowel not immediately before the s.
    if (!$found && self::hasEnding('s') && self::containsVowel(substr(self::$word, 0, -2))) {
      self::removeEnding('s');
    }
  }

  /**
   * Handles various suffixes, of which the longest is replaced.
   *
   * Implements step 1b of the Porter2 algorithm.
   */
  protected static function step1b() {
    $exceptions = array(
      'inning',
      'outing',
      'canning',
      'herring',
      'earring',
      'proceed',
      'exceed',
      'succeed',
    );
    if (in_array(self::$word, $exceptions)) {
      return;
    }
    $checks = array('eedly', 'eed');
    foreach ($checks as $check) {
      if (self::hasEnding($check)) {
        if (self::$r1 !== self::length()) {
          self::removeEnding($check);
          self::addEnding('ee');
        }
        return;
      }
    }
    $checks = array('ingly', 'edly', 'ing', 'ed');
    $second_endings = array('at', 'bl', 'iz');
    foreach ($checks as $check) {
      // If the ending is present and the previous part contains a vowel.
      if (self::hasEnding($check) && self::containsVowel(substr(self::$word, 0, -strlen($check)))) {
        self::removeEnding($check);
        foreach ($second_endings as $ending) {
          if (self::hasEnding($ending)) {
            self::addEnding('e');
            return;
          }
        }
        // If the word ends with a double, remove the last letter.
        $found = self::removeDoubles();
        // If the word is short, add e (so hop -> hope).
        if (!$found && (self::isShort())) {
          self::addEnding('e');
        }
        return;
      }
    }
  }

  /**
   * Replaces suffix y or Y with i if after non-vowel not @ word begin.
   *
   * Implements step 1c of the Porter2 algorithm.
   */
  protected static function step1c() {
    if ((self::hasEnding('y') || self::hasEnding('Y')) && self::length() > 2 && !(self::isVowel(self::length() - 2))) {
      self::removeEnding('y');
      self::addEnding('i');
    }
  }

  /**
   * Implements step 2 of the Porter2 algorithm.
   */
  protected static function step2() {
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
      if (self::hasEnding($find)) {
        if (self::inR1($find)) {
          self::removeEnding($find);
          self::addEnding($replace);
        }
        return;
      }
    }
    if (self::hasEnding('li')) {
      if (self::length() > 4 && self::validLi(self::charAt(-3))) {
        self::removeEnding('li');
      }
    }
  }

  /**
   * Implements step 3 of the Porter2 algorithm.
   */
  protected static function step3() {
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
      if (self::hasEnding($find)) {
        if (self::inR1($find)) {
          self::removeEnding($find);
          self::addEnding($replace);
        }
        return;
      }
    }
    if (self::hasEnding('ative')) {
      if (self::inR2('ative')) {
        self::removeEnding('ative');
      }
    }
  }

  /**
   * Implements step 4 of the Porter2 algorithm.
   */
  protected static function step4() {
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
      if (self::hasEnding($check)) {
        if (self::inR2($check)) {
          if ($check !== 'ion' || in_array(self::charAt(-4), array('s', 't'))) {
            self::removeEnding($check);
          }
        }
        return;
      }
    }
  }

  /**
   * Implements step 5 of the Porter2 algorithm.
   */
  protected static function step5() {
    if (self::hasEnding('e')) {
      // Delete if in R2, or in R1 and not preceded by a short syllable.
      if (self::inR2('e') || (self::inR1('e') && !self::isShortSyllable(self::length() - 3))) {
        self::removeEnding('e');
      }
      return;
    }
    if (self::hasEnding('l')) {
      // Delete if in R2 and preceded by l.
      if (self::inR2('l') && self::charAt(-2) == 'l') {
        self::removeEnding('l');
      }
    }
  }

  /**
   * Removes certain double consonants from the word's end.
   *
   * @return bool
   *   TRUE if a match was found and removed, FALSE otherwise.
   */
  protected static function removeDoubles() {
    $found = FALSE;
    $doubles = array('bb', 'dd', 'ff', 'gg', 'mm', 'nn', 'pp', 'rr', 'tt');
    foreach ($doubles as $double) {
      if (substr(self::$word, -2) == $double) {
        self::$word = substr(self::$word, 0, -1);
        $found = TRUE;
        break;
      }
    }
    return $found;
  }

  /**
   * Checks whether a character is a vowel.
   *
   * @param int $position
   *   The character's position.
   * @param string|null $word
   *   (optional) The word in which to check. Defaults to self::$word.
   * @param string[] $additional
   *   (optional) Additional characters that should count as vowels.
   *
   * @return bool
   *   TRUE if the character is a vowel, FALSE otherwise.
   */
  protected static function isVowel($position, $word = NULL, $additional = array()) {
    if ($word === NULL) {
      $word = self::$word;
    }
    $vowels = array_merge(array('a', 'e', 'i', 'o', 'u', 'y'), $additional);
    return in_array(self::charAt($position, $word), $vowels);
  }

  /**
   * Retrieves the character at the given position.
   *
   * @param int $position
   *   The 0-based index of the character. If a negative number is given, the
   *   position is counted from the end of the string.
   * @param string|null $word
   *   (optional) The word from which to retrieve the character. Defaults to
   *   self::$word.
   *
   * @return string
   *   The character at the given position, or an empty string if the given
   *   position was illegal.
   */
  protected static function charAt($position, $word = NULL) {
    if ($word === NULL) {
      $word = self::$word;
    }
    $length = strlen($word);
    if (abs($position) >= $length) {
      return '';
    }
    if ($position < 0) {
      $position += $length;
    }
    return $word[$position];
  }

  /**
   * Determines whether the word ends in a "vowel-consonant" suffix.
   *
   * Unless the word is only two characters long, it also checks that the
   * third-last character is neither "w", "x" nor "Y".
   *
   * @param int|null $position
   *   (optional) If given, do not check the end of the word, but the character
   *   at the given position, and the next one.
   *
   * @return bool
   *   TRUE if the word has the described suffix, FALSE otherwise.
   */
  protected static function isShortSyllable($position = NULL) {
    if ($position === NULL) {
      $position = self::length() - 2;
    }
    // A vowel at the beginning of the word followed by a non-vowel.
    if ($position === 0) {
      return self::isVowel(0) && !self::isVowel(1);
    }
    // Vowel followed by non-vowel other than w, x, Y and preceded by
    // non-vowel.
    $additional = array('w', 'x', 'Y');
    return !self::isVowel($position - 1) && self::isVowel($position) && !self::isVowel($position + 1, NULL, $additional);
  }

  /**
   * Determines whether the word is short.
   *
   * A word is called short if it ends in a short syllable and if R1 is null.
   *
   * @return bool
   *   TRUE if the word is short, FALSE otherwise.
   */
  protected static function isShort() {
    return self::isShortSyllable() && self::$r1 == self::length();
  }

  /**
   * Determines the start of a certain "R" region.
   *
   * R is a region after the first non-vowel following a vowel, or end of word.
   *
   * @param int $type
   *   (optional) 1 or 2. If 2, then calculate the R after the R1.
   *
   * @return int
   *   The R position.
   */
  protected static function R($type = 1) {
    $inc = 1;
    if ($type === 2) {
      $inc = self::$r1;
    }
    elseif (self::length() > 5) {
      $prefix_5 = substr(self::$word, 0, 5);
      if ($prefix_5 === 'gener' || $prefix_5 === 'arsen') {
        return 5;
      }
      if (self::length() > 6 && substr(self::$word, 0, 6) === 'commun') {
        return 6;
      }
    }

    while ($inc <= self::length()) {
      if (!self::isVowel($inc) && self::isVowel($inc - 1)) {
        $position = $inc;
        break;
      }
      $inc++;
    }
    if (!isset($position)) {
      $position = self::length();
    }
    else {
      // We add one, as this is the position AFTER the first non-vowel.
      $position++;
    }
    return $position;
  }

  /**
   * Checks whether the given string is contained in R1.
   *
   * @param string $string
   *   The string.
   *
   * @return bool
   *   TRUE if the string is in R1, FALSE otherwise.
   */
  protected static function inR1($string) {
    $r1 = substr(self::$word, self::$r1);
    return strpos($r1, $string) !== FALSE;
  }

  /**
   * Checks whether the given string is contained in R2.
   *
   * @param string $string
   *   The string.
   *
   * @return bool
   *   TRUE if the string is in R2, FALSE otherwise.
   */
  protected static function inR2($string) {
    $r2 = substr(self::$word, self::$r2);
    return strpos($r2, $string) !== FALSE;
  }

  /**
   * Determines the string length of the current word.
   *
   * @return int
   *   The string length of the current word.
   */
  protected static function length() {
    return strlen(self::$word);
  }

  /**
   * Checks whether the word ends with the given string.
   *
   * @param string $string
   *   The string.
   *
   * @return bool
   *   TRUE if the word ends with the given string, FALSE otherwise.
   */
  protected static function hasEnding($string) {
    $length = strlen($string);
    if ($length > self::length()) {
      return FALSE;
    }
    return (substr_compare(self::$word, $string, -1 * $length, $length) === 0);
  }

  /**
   * Appends a given string to the current word.
   *
   * @param string $string
   *   The ending to append.
   */
  protected static function addEnding($string) {
    self::$word = self::$word . $string;
  }

  /**
   * Removes a given string from the end of the current word.
   *
   * Does not check whether the ending is actually there.
   *
   * @param string $string
   *   The ending to remove.
   */
  protected static function removeEnding($string) {
    self::$word = substr(self::$word, 0, -strlen($string));
  }

  /**
   * Checks whether the given string contains a vowel.
   *
   * @param string $string
   *   The string to check.
   *
   * @return bool
   *   TRUE if the string contains a vowel, FALSE otherwise.
   */
  protected static function containsVowel($string) {
    $inc = 0;
    $return = FALSE;
    while ($inc < strlen($string)) {
      if (self::isVowel($inc, $string)) {
        $return = TRUE;
        break;
      }
      $inc++;
    }
    return $return;
  }

  /**
   * Checks whether the given string is a valid -li prefix.
   *
   * @param string $string
   *   The string to check.
   *
   * @return bool
   *   TRUE if the given string is a valid -li prefix, FALSE otherwise.
   */
  protected static function validLi($string) {
    return in_array($string, array(
      'c',
      'd',
      'e',
      'g',
      'h',
      'k',
      'm',
      'n',
      'r',
      't',
    ));
  }

}
