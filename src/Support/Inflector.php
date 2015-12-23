<?php

namespace AdamStipak\Support;

use Nette\Utils\Strings;

class Inflector {

  /**
   * Converts the given string to `StudlyCase`
   * @param string $string
   * @return string
   */
  public static function studlyCase($string) {
    $string = Strings::capitalize(Strings::replace($string, ['/-/', '/_/'], ' '));
    return Strings::replace($string, '/ /');
  }

  /**
   * Converts the given string to `spinal-case`
   * @param string $string
   * @return string
   */
  public static function spinalCase($string) {
    /** RegExp source http://stackoverflow.com/a/1993772 */
    preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $string, $matches);
    $matches = $matches[0];
    foreach ($matches as &$match) {
      $match = ($match == Strings::upper($match)) ? Strings::lower($match) : Strings::firstLower($match);
    }
    return implode('-', $matches);
  }
}
