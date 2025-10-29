<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// LANDING
$app->get('/', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $html = $twig->render('landing.html.twig', ['name' => 'Onaolapo Adesoye']);
    $response->getBody()->write($html);
    return $response;
});

// AUTH PAGE (LOGIN + SIGNUP)
$app->get('/auth', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $html = $twig->render('auth/page.html.twig');
    $response->getBody()->write($html);
    return $response;
});

// LOGIN SUBMIT
$app->post('/auth/login', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($email === 'user@example.com' && $password === 'password') {
        $_SESSION['user'] = true;
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    $html = $twig->render('auth/page.html.twig', [
        'active' => 'login',
        'error' => 'Invalid email or password'
    ]);
    $response->getBody()->write($html);
    return $response;
});

// SIGNUP SUBMIT
$app->post('/auth/signup', function (Request $request, Response $response) {
    $twig = $this->get('view');
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($email && $password) {
        $_SESSION['user'] = true;
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    $html = $twig->render('auth/page.html.twig', [
        'active' => 'signup',
        'error' => 'Please fill all fields'
    ]);
    $response->getBody()->write($html);
    return $response;
});

// LOGOUT
$app->get('/logout', function (Request $request, Response $response) {
    unset($_SESSION['user']);
    return $response->withHeader('Location', '/')->withStatus(302);
});
