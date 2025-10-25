<?php
namespace App;

class Csrf {
  public static function token(): string {
    if (empty($_SESSION['csrf'])) {
      $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
  }
  public static function assert(): void {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
      Flash::push(['type'=>'error','message'=>'Invalid request token. Please retry.']);
      header('Location: /tickets'); exit;
    }
  }
}
