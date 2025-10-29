<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// ---------------------
// LANDING PAGE
// ---------------------
$app->get('/', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $html = $twig->render('landing.html.twig', [
        'title' => 'Welcome to TicketFlow'
    ]);
    $response->getBody()->write($html);
    return $response;
});

// ---------------------
// AUTH PAGE (LOGIN + SIGNUP VIEW)
// ---------------------
$app->get('/auth', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $query = $request->getQueryParams();
    $mode = $query['mode'] ?? 'login';

    $html = $twig->render('auth/page.html.twig', [
        'is_login' => $mode === 'login',
        'errors' => [],
        'form' => []
    ]);
    $response->getBody()->write($html);
    return $response;
});

// ---------------------
// LOGIN SUBMIT
// ---------------------
$app->post('/auth/login', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $data = $request->getParsedBody();

    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    $errors = [];

    if (empty($email)) $errors['email'] = 'Email is required.';
    if (empty($password)) $errors['password'] = 'Password is required.';

    // Dummy credentials (replace with DB later)
    if (empty($errors) && !($email === 'user@example.com' && $password === 'password')) {
        $errors['email'] = 'Invalid email or password.';
    }

    if (!empty($errors)) {
        $html = $twig->render('auth/page.html.twig', [
            'is_login' => true,
            'errors' => $errors,
            'form' => $data
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    $_SESSION['user'] = ['email' => $email];
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});

// ---------------------
// SIGNUP SUBMIT
// ---------------------
$app->post('/auth/signup', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $data = $request->getParsedBody();

    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    $confirm = trim($data['confirm_password'] ?? '');
    $errors = [];

    if (empty($email)) $errors['email'] = 'Email is required.';
    if (empty($password)) $errors['password'] = 'Password is required.';
    if ($password !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';

    if (!empty($errors)) {
        $html = $twig->render('auth/page.html.twig', [
            'is_login' => false,
            'errors' => $errors,
            'form' => $data
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    // In production, you'd save to a database here
    $_SESSION['user'] = ['email' => $email];

    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});

// ---------------------
// DASHBOARD
// ---------------------
$app->get('/dashboard', function (Request $request, Response $response) {
    if (empty($_SESSION['user'])) {
        return $response->withHeader('Location', '/auth')->withStatus(302);
    }

    $twig = $this->get('view');
    $tickets = json_decode(file_get_contents('tickets.json'), true) ?: [];

    $stats = array_reduce($tickets, function ($acc, $t) {
        $acc['total']++;
        if ($t['status'] === 'open') $acc['open']++;
        if ($t['status'] === 'in_progress') $acc['in_progress']++;
        if ($t['status'] === 'closed') $acc['closed']++;
        return $acc;
    }, ['total' => 0, 'open' => 0, 'in_progress' => 0, 'closed' => 0]);

    $recent = array_slice($tickets, -5);
    $profile = json_decode(file_get_contents('profile.json'), true) ?: [];

    $html = $twig->render('dashboard/page.html.twig', [
        'stats' => $stats,
        'recent_tickets' => $recent,
        'profile' => $profile,
        'user' => $_SESSION['user']
    ]);
    $response->getBody()->write($html);
    return $response;
});

// ---------------------
// PROFILE UPDATE
// ---------------------
$app->post('/dashboard/profile', function (Request $request, Response $response) {
    if (empty($_SESSION['user'])) {
        return $response->withHeader('Location', '/auth')->withStatus(302);
    }

    $data = $request->getParsedBody();
    file_put_contents('profile.json', json_encode([
        'name' => $data['name'] ?? '',
        'email' => $data['email'] ?? ''
    ]));

    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});

// ---------------------
// LOGOUT
// ---------------------
$app->get('/logout', function (Request $request, Response $response) {
    session_destroy();
    return $response->withHeader('Location', '/')->withStatus(302);
});

// ---------------------
// FALLBACK MIDDLEWARE (Avoid 405 Errors)
// ---------------------
$app->add(function ($request, $handler) {
    $uri = $request->getUri()->getPath();
    $method = $request->getMethod();

    // Allow GET and POST for /auth
    if ($uri === '/auth' && !in_array($method, ['GET', 'POST'])) {
        $response = new \Slim\Psr7\Response();
        return $response->withHeader('Location', '/auth')->withStatus(302);
    }

    return $handler->handle($request);
});
