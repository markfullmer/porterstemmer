<?php

namespace Drupal\Tests\porterstemmer\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\porterstemmer\Porter2;

/**
 * Tests the "PorterStemmer" implementation.
 *
 * @coversDefaultClass \Drupal\porterstemmer\Porter2
 * @group porterstemmer
 *
 * @see \Drupal\porterstemmer\Porter2
 */
class Porter2Test extends UnitTestCase {

  /**
   * Test AddClass::add() with a data provider method.
   *
   * Uses the data provider method to test with a wide range of words/stems.
   *
   * @dataProvider addDataProvider
   *
   * @see AddClassTest::addDataProvider()
   */
  public function testAddWithDataProvider($word, $stem) {
    $this->assertEquals($stem, Porter2::stem($word));
  }

  /**
   * Data provider for testAddWithDataProvider().
   *
   * Data provider methods take no arguments and return an array of data
   * to use for tests. Each element of the array is another array, which
   * corresponds to the arguments in the test method's signature.
   *
   * Note also that PHPUnit tries to run tests using methods that begin
   * with 'test'. This means that data provider method names should not
   * begin with 'test'. Also, by convention, they should end with
   * 'DataProvider'.
   *
   * @return array
   *   Nested arrays of values to check:
   *   - $word
   *   - $stem
   *
   * @see AddClassTest::testAddWithDataProvider()
   */
  public function addDataProvider() {
    return array(
      array('TestCharacters@#/#$%^*()', 'testcharacters@#/#$%^*()'),
      array('test space', 'test spac'),
      array('ÇELIK', 'Çelik'),
      array('Maric&#xF3;n', 'maric&#xf3;n'),
      array('Yo', 'yo'),
      array('ties', 'tie'),
      array('cries', 'cri'),
      array('exceed', 'exceed'),
      array('consign', 'consign'),
      array('consigned', 'consign'),
      array('consigning', 'consign'),
      array('consignment', 'consign'),
      array('consist', 'consist'),
      array('consisted', 'consist'),
      array('consistency', 'consist'),
      array('consistent', 'consist'),
      array('consistently', 'consist'),
      array('consisting', 'consist'),
      array('consists', 'consist'),
      array('consolation', 'consol'),
      array('consolations', 'consol'),
      array('consolatory', 'consolatori'),
      array('console', 'consol'),
      array('consoled', 'consol'),
      array('consoles', 'consol'),
      array('consolidate', 'consolid'),
      array('consolidated', 'consolid'),
      array('consolidating', 'consolid'),
      array('consoling', 'consol'),
      array('consolingly', 'consol'),
      array('consols', 'consol'),
      array('consonant', 'conson'),
      array('consort', 'consort'),
      array('consorted', 'consort'),
      array('consorting', 'consort'),
      array('conspicuous', 'conspicu'),
      array('conspicuously', 'conspicu'),
      array('conspiracy', 'conspiraci'),
      array('conspirator', 'conspir'),
      array('conspirators', 'conspir'),
      array('conspire', 'conspir'),
      array('conspired', 'conspir'),
      array('conspiring', 'conspir'),
      array('constable', 'constabl'),
      array('constables', 'constabl'),
      array('constance', 'constanc'),
      array('constancy', 'constanc'),
      array('constant', 'constant'),
      array('knack', 'knack'),
      array('knackeries', 'knackeri'),
      array('knacks', 'knack'),
      array('knag', 'knag'),
      array('knave', 'knave'),
      array('knaves', 'knave'),
      array('knavish', 'knavish'),
      array('kneaded', 'knead'),
      array('kneading', 'knead'),
      array('knee', 'knee'),
      array('kneel', 'kneel'),
      array('kneeled', 'kneel'),
      array('kneeling', 'kneel'),
      array('kneels', 'kneel'),
      array('knees', 'knee'),
      array('knell', 'knell'),
      array('knelt', 'knelt'),
      array('knew', 'knew'),
      array('knick', 'knick'),
      array('knif', 'knif'),
      array('knife', 'knife'),
      array('knight', 'knight'),
      array('knightly', 'knight'),
      array('knights', 'knight'),
      array('knit', 'knit'),
      array('knits', 'knit'),
      array('knitted', 'knit'),
      array('knitting', 'knit'),
      array('knives', 'knive'),
      array('knob', 'knob'),
      array('knobs', 'knob'),
      array('knock', 'knock'),
      array('knocked', 'knock'),
      array('knocker', 'knocker'),
      array('knockers', 'knocker'),
      array('knocking', 'knock'),
      array('knocks', 'knock'),
      array('knopp', 'knopp'),
      array('knot', 'knot'),
      array('knots', 'knot'),
    );
  }

}
