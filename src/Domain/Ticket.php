<?php
namespace App\Domain;

class Ticket {
  public static function new(array $data): array {
    $now = date('c');
    return [
      'id' => (string)(microtime(true)*1000),
      'title' => $data['title'],
      'description' => $data['description'] ?? '',
      'status' => $data['status'],
      'priority' => $data['priority'] ?? 'medium',
      'createdAt' => $now,
      'updatedAt' => $now
    ];
  }
}
