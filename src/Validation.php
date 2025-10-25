<?php
namespace App;

class Validation {
  public static function requireFields(array $keys): void {
    foreach ($keys as $k) {
      if (!isset($_POST[$k]) || trim((string)$_POST[$k]) === '') {
        Flash::push(['type'=>'error','message'=>"{$k} is required"]);
        header($_SERVER['HTTP_REFERER'] ?? 'Location: /'); exit;
      }
    }
  }
  public static function inArray(string $key, array $allowed): void {
    if (!in_array($_POST[$key] ?? null, $allowed, true)) {
      Flash::push(['type'=>'error','message'=>"$key must be one of: ".implode(', ',$allowed)]);
      header($_SERVER['HTTP_REFERER'] ?? 'Location: /'); exit;
    }
  }
}
