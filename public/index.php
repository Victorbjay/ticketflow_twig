<?php
declare(strict_types=1);

use App\Router;
use App\Auth;
use App\Flash;
use App\Csrf;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

$router = new Router();

// ---------- PAGES (GET)
$router->get('/', function() use ($twig) {
  echo $twig->render('landing.twig', [
    'title' => 'ResolveHub — Manage Tickets Like a Pro'
  ]);
});

$router->get('/auth/login', function() use ($twig) {
  echo $twig->render('auth_login.twig', [
    'title' => 'Login — ResolveHub',
    'flash' => App\Flash::pull()
  ]);
});

$router->get('/auth/signup', function() use ($twig) {
  echo $twig->render('auth_signup.twig', [
    'title' => 'Create Account — ResolveHub',
    'flash' => App\Flash::pull()
  ]);
});

$router->get('/dashboard', function() use ($twig) {
  Auth::requireAuth('/auth/login');
  $tickets = (new TicketRepository())->all();

  $stats = [
    'total' => count($tickets),
    'open' => count(array_filter($tickets, fn($t) => $t['status']==='open')),
    'in_progress' => count(array_filter($tickets, fn($t) => $t['status']==='in_progress')),
    'closed' => count(array_filter($tickets, fn($t) => $t['status']==='closed')),
  ];

  echo $twig->render('dashboard.twig', [
    'title' => 'Dashboard — ResolveHub',
    'user' => Auth::user(),
    'stats' => $stats,
    'flash' => Flash::pull()
  ]);
});

$router->get('/tickets', function() use ($twig) {
  Auth::requireAuth('/auth/login');
  $repo = new TicketRepository();
  echo $twig->render('tickets.twig', [
    'title' => 'Tickets — ResolveHub',
    'user' => Auth::user(),
    'tickets' => $repo->all(),
    'csrf' => Csrf::token(),
    'flash' => Flash::pull()
  ]);
});

// ---------- AUTH (POST)
$router->post('/auth/login', function() {
  App\Validation::requireFields(['email','password']);
  $users = new UserRepository();
  if ($users->verify($_POST['email'], $_POST['password'])) {
    App\Auth::login(['email'=>$_POST['email']]);
    App\Flash::push(['type'=>'success','message'=>'Login successful! Redirecting...']);
    header('Location: /dashboard'); exit;
  }
  App\Flash::push(['type'=>'error','message'=>'Invalid credentials. Please try again.']);
  header('Location: /auth/login'); exit;
});

$router->post('/auth/signup', function() {
  App\Validation::requireFields(['email','password','confirm_password']);
  if ($_POST['password'] !== $_POST['confirm_password']) {
    App\Flash::push(['type'=>'error','message'=>'Passwords do not match']);
    header('Location: /auth/signup'); exit;
  }
  $users = new UserRepository();
  if ($users->exists($_POST['email'])) {
    App\Flash::push(['type'=>'error','message'=>'Email already registered']);
    header('Location: /auth/signup'); exit;
  }
  $users->create($_POST['email'], $_POST['password']);
  App\Auth::login(['email'=>$_POST['email']]);
  App\Flash::push(['type'=>'success','message'=>'Account created! Redirecting...']);
  header('Location: /dashboard'); exit;
});

// ---------- TICKETS (POST) — CRUD
$router->post('/tickets/create', function() {
  Auth::requireAuth('/auth/login');
  Csrf::assert();
  App\Validation::requireFields(['title','status']);
  App\Validation::inArray('status',['open','in_progress','closed']);

  $repo = new TicketRepository();
  $repo->create([
    'title'=> trim($_POST['title']),
    'description'=> trim($_POST['description'] ?? ''),
    'status'=> $_POST['status'],
    'priority'=> $_POST['priority'] ?? 'medium'
  ]);

  Flash::push(['type'=>'success','message'=>'Ticket created successfully!']);
  header('Location: /tickets'); exit;
});

$router->post('/tickets/update', function() {
  Auth::requireAuth('/auth/login');
  Csrf::assert();
  App\Validation::requireFields(['id','title','status']);
  App\Validation::inArray('status',['open','in_progress','closed']);

  $repo = new TicketRepository();
  $ok = $repo->update($_POST['id'], [
    'title'=> trim($_POST['title']),
    'description'=> trim($_POST['description'] ?? ''),
    'status'=> $_POST['status'],
    'priority'=> $_POST['priority'] ?? 'medium'
  ]);

  if ($ok) Flash::push(['type'=>'success','message'=>'Ticket updated successfully!']);
  else Flash::push(['type'=>'error','message'=>'Failed to update ticket. Please retry.']);

  header('Location: /tickets'); exit;
});

$router->post('/tickets/delete', function() {
  Auth::requireAuth('/auth/login');
  Csrf::assert();
  App\Validation::requireFields(['id']);

  $repo = new TicketRepository();
  $ok = $repo->delete($_POST['id']);

  if ($ok) Flash::push(['type'=>'success','message'=>'Ticket deleted successfully!']);
  else Flash::push(['type'=>'error','message'=>'Failed to delete ticket. Please retry.']);

  header('Location: /tickets'); exit;
});

// ---------- LOGOUT
$router->post('/logout', function() {
  App\Auth::logout();
  App\Flash::push(['type'=>'success','message'=>'You have been logged out.']);
  header('Location: /auth/login'); exit;
});

// ---------- DISPATCH
$router->dispatch();
