<?php
declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

session_start();

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
  'cache' => false, // enable in prod
  'autoescape' => 'html'
]);

// Expose theme variables or helpers globally if desired
$twig->addGlobal('APP_NAME', 'ResolveHub');
