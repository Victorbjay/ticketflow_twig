<?php
namespace App;

class Flash {
  public static function push(array $msg): void {
    $_SESSION['flash'][] = $msg;
  }
  public static function pull(): array {
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
  }
}
