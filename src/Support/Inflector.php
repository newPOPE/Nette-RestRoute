<?php

namespace AdamStipak\Support;

class Inflector
{
  /**
   * Converts the given string to `camelCase`
   * @param string $string
   * @return string
   */
  public static function camelCase($string) {
    return lcfirst(static::studlyCase($string));
  }

  /**
   * Converts the given string to `StudlyCase`
   * @param string $string
   * @return string
   */
  public static function studlyCase($string) {
    $value = ucwords(str_replace(array('-', '_'), ' ', $string));
    return str_replace(' ', '', $value);
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
      $match = ($match == strtoupper($match)) ? strtolower($match) : lcfirst($match);
    }
    return implode('-', $matches);
  }
}
