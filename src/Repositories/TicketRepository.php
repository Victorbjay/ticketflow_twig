<?php
namespace App\Repositories;

use App\Domain\Ticket;

class TicketRepository {
  private string $file;
  public function __construct() {
    $this->file = __DIR__ . '/../../data/tickets.json';
    if (!file_exists($this->file)) file_put_contents($this->file, json_encode([]));
  }
  private function read(): array {
    return json_decode(file_get_contents($this->file), true) ?: [];
  }
  private function write(array $items): void {
    file_put_contents($this->file, json_encode(array_values($items), JSON_PRETTY_PRINT));
  }
  public function all(): array {
    return $this->read();
  }
  public function create(array $data): void {
    $items = $this->read();
    $items[] = Ticket::new($data);
    $this->write($items);
  }
  public function update(string $id, array $patch): bool {
    $items = $this->read();
    $found = false;
    foreach ($items as &$t) {
      if ($t['id'] === $id) {
        $t = array_merge($t, $patch, ['updatedAt'=>date('c')]);
        $found = true; break;
      }
    }
    if ($found) $this->write($items);
    return $found;
  }
  public function delete(string $id): bool {
    $items = $this->read();
    $countBefore = count($items);
    $items = array_filter($items, fn($t)=>$t['id'] !== $id);
    $this->write($items);
    return count($items) < $countBefore;
  }
}
