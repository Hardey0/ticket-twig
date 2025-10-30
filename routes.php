<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// ==============================
// LANDING PAGE
// ==============================
$app->get('/', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $html = $twig->render('landing.html.twig');
    $response->getBody()->write($html);
    return $response;
});

// ==============================
// AUTH PAGES
// ==============================
$app->get('/auth', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $query = $request->getQueryParams();
    $active = $query['mode'] ?? 'login';

    $html = $twig->render('auth/page.html.twig', [
        'active' => $active
    ]);
    $response->getBody()->write($html);
    return $response;
});

$app->post('/auth', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $data = $request->getParsedBody();

    $mode = $data['mode'] ?? 'login';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $confirm = $data['confirm_password'] ?? '';

    $errors = [];

    // --- LOGIN ---
    if ($mode === 'login') {
        if ($email !== 'user@example.com' || $password !== 'password') {
            $errors[] = 'Invalid email or password';
        }
    }
    // --- SIGNUP ---
    else {
        if (!$email || !$password || $password !== $confirm) {
            $errors[] = $password !== $confirm
                ? 'Passwords do not match'
                : 'Please fill all fields';
        }
    }

    // --- SUCCESS ---
    if (empty($errors)) {
        $_SESSION['user'] = true;
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    // --- FAILURE ---
    $html = $twig->render('auth/page.html.twig', [
        'active' => $mode,
        'error'  => $errors[0]
    ]);
    $response->getBody()->write($html);
    return $response;
});

// ==============================
// DASHBOARD PAGE (Protected)
// ==============================
$app->get('/dashboard', function (Request $request, Response $response) {
    if (empty($_SESSION['user'])) {
        return $response->withHeader('Location', '/auth')->withStatus(302);
    }

    $twig = $this->get('view');

    // Example stats (optional, can be dynamic)
    $stats = [
        'total' => 0,
        'open' => 0,
        'in_progress' => 0,
        'closed' => 0,
    ];

    $html = $twig->render('dashboard/page.html.twig', [
        'stats' => $stats
    ]);
    $response->getBody()->write($html);
    return $response;
});

// ==============================
// LOGOUT
// ==============================
$app->map(['GET', 'POST'], '/logout', function (Request $request, Response $response) {
    unset($_SESSION['user']);
    return $response->withHeader('Location', '/')->withStatus(302);
});

// ==============================
// TICKETS PAGE (Protected)
// ==============================
$app->map(['GET', 'POST'], '/tickets', function (Request $request, Response $response) {
    if (empty($_SESSION['user'])) {
        return $response->withHeader('Location', '/auth')->withStatus(302);
    }

    $twig = $this->get('view');

    // Read query params for tab and filter
    $query = $request->getQueryParams();
    $tab = $query['tab'] ?? 'create';       // default to "create" tab when coming from dashboard
    $filter = $query['filter'] ?? 'all';
    $ticketId = $query['id'] ?? null;

    // Optional: handle edit_ticket if ?tab=edit&id=...
    $edit_ticket = null;
    if ($tab === 'edit' && $ticketId) {
        // Normally, fetch from DB. For now, empty object
        $edit_ticket = [
            'id' => $ticketId,
            'title' => '',
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => '',
            'date' => date('Y-m-d')
        ];
    }

    // Pass minimal context to Twig to prevent errors
    $html = $twig->render('tickets/page.html.twig', [
        'tab' => $tab,
        'filter' => $filter,
        'edit_ticket' => $edit_ticket,
        'filtered_tickets' => [], // empty by default
        'stats' => [
            'total' => 0,
            'open' => 0,
            'in_progress' => 0,
            'closed' => 0
        ]
    ]);

    $response->getBody()->write($html);
    return $response;
});
