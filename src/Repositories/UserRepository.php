<?php
namespace App\Repositories;

class UserRepository {
  private string $file;
  public function __construct() {
    $this->file = __DIR__ . '/../../data/users.json';
    if (!file_exists($this->file)) {
      file_put_contents($this->file, json_encode([[
        'email'=>'demo@resolvehub.com',
        'password'=>password_hash('demo123', PASSWORD_DEFAULT)
      ]], JSON_PRETTY_PRINT));
    }
  }
  private function read(): array {
    return json_decode(file_get_contents($this->file), true) ?: [];
  }
  private function write(array $items): void {
    file_put_contents($this->file, json_encode(array_values($items), JSON_PRETTY_PRINT));
  }
  public function exists(string $email): bool {
    foreach ($this->read() as $u) if ($u['email']===$email) return true;
    return false;
  }
  public function create(string $email, string $password): void {
    $items = $this->read();
    $items[] = ['email'=>$email, 'password'=>password_hash($password, PASSWORD_DEFAULT)];
    $this->write($items);
  }
  public function verify(string $email, string $password): bool {
    foreach ($this->read() as $u) {
      if ($u['email']===$email && password_verify($password, $u['password'])) return true;
    }
    return false;
  }
}
