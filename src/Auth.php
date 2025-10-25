<?php
namespace App;

class Auth {
  public static function login(array $user): void {
    $_SESSION['session_token'] = 'mock-jwt-'.time();
    $_SESSION['user'] = $user;
  }
  public static function logout(): void {
    unset($_SESSION['session_token'], $_SESSION['user']);
  }
  public static function check(): bool {
    return isset($_SESSION['session_token']);
  }
  public static function user(): ?array {
    return $_SESSION['user'] ?? null;
  }
  public static function requireAuth(string $redirect): void {
    if (!self::check()) {
      Flash::push(['type'=>'error','message'=>'Your session has expired — please log in again.']);
      header("Location: {$redirect}"); exit;
    }
  }
}
