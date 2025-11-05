<?php
declare(strict_types=1);

namespace WebEngine\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use WebEngine\Services\AuthService;
use WebEngine\Services\EmailService;
use WebEngine\Database\Database;

class AuthController
{
    private Twig $view;
    private AuthService $auth;
    private EmailService $email;
    private Database $db;

    public function __construct(Twig $view, AuthService $auth, EmailService $email, Database $db)
    {
        $this->view = $view;
        $this->auth = $auth;
        $this->email = $email;
        $this->db = $db;
    }

    public function showLogin(Request $request, Response $response): Response
    {
        if ($this->auth->check()) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->auth->attempt($username, $password)) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $this->view->render($response, 'auth/login.twig', [
            'error' => 'Invalid username or password'
        ]);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        if ($this->auth->check()) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => 'All fields are required'
            ]);
        }

        if ($data['password'] !== $data['password_confirm']) {
            return $this->view->render($response, 'auth/register.twig', [
                'error' => 'Passwords do not match'
            ]);
        }

        if ($this->auth->register($data)) {
            // Send welcome email
            $this->email->sendWelcomeEmail($data['email'], $data['username']);

            return $this->view->render($response, 'auth/login.twig', [
                'success' => 'Account created successfully! You can now login.'
            ]);
        }

        return $this->view->render($response, 'auth/register.twig', [
            'error' => 'Username or email already exists'
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->auth->logout();
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function showForgotPassword(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'auth/forgot-password.twig');
    }

    public function forgotPassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';

        $sql = "SELECT * FROM MEMB_INFO WHERE memb___id = ?";
        $account = $this->db->fetch($sql, [$username]);

        if ($account) {
            $token = $this->auth->createPasswordResetToken($username);
            $this->email->sendPasswordResetEmail($account['mail_addr'], $username, $token);
        }

        return $this->view->render($response, 'auth/forgot-password.twig', [
            'success' => 'If the account exists, a password reset email has been sent.'
        ]);
    }

    public function verifyEmail(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'] ?? '';

        // Verify token logic here
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
