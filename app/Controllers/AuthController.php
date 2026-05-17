<?php

class AuthController extends Controller {

    public function loginForm(): void {
        if (Auth::check()) {
            $this->redirect(Auth::isAdminAuthorized() ? 'admin-dashboard' : 'dashboard');
        }
        $this->view('auth/login', [
            'title'  => 'Sign In',
            'layout' => 'auth',
        ]);
    }

    public function login(): void {
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('login');
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = new Validator($_POST);
        $validator->required('email', 'Email')
                  ->email('email', 'Email')
                  ->required('password', 'Password');

        if (!$validator->passes()) {
            setFlash('error', $validator->firstError());
            $this->redirect('login');
        }

        if (Auth::attempt($email, $password)) {
            session_regenerate_id(true);
            setFlash('success', 'Welcome back, ' . Auth::user()['name'] . '!');
            $this->redirect(Auth::isAdminAuthorized() ? 'admin-dashboard' : 'dashboard');
        }

        setFlash('error', 'Invalid email or password.');
        $this->redirect('login');
    }

    public function registerForm(): void {
        if (Auth::check()) {
            $this->redirect('catalog');
        }
        $this->view('auth/register', [
            'title'  => 'Create Account',
            'layout' => 'auth',
        ]);
    }

    public function register(): void {
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request.');
            $this->redirect('register');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'Full Name')
                  ->required('email', 'Email')
                  ->email('email', 'Email')
                  ->required('password', 'Password')
                  ->minLength('password', 6, 'Password')
                  ->matches('password', 'password_confirmation', 'Password');

        if (!$validator->passes()) {
            setFlash('error', $validator->firstError());
            $this->redirect('register');
        }

        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if (Auth::register($name, $email, $password)) {
            setFlash('success', 'Account created! Welcome to ' . APP_NAME . '.');
            try {
                require_once APP_PATH . '/Models/Notification.php';
                $adminId = 1; // معرف الآدمن الرئيسي

                Notification::create(
                    $adminId,
                    "📢 New user registered: {$name} ({$email})",
                    'admin_alert'
                );
            } catch (Throwable $e) {
                error_log("Failed to send admin notification: " . $e->getMessage());
            }
            $this->redirect('login');
        }

        setFlash('error', 'Email already registered.');
        $this->redirect('register');
    }

    public function forgotForm(): void {
        $this->view('auth/forgot', [
            'title'  => 'Reset Password',
            'layout' => 'auth',
        ]);
    }

    public function forgot(): void {
        setFlash('success', 'If that email exists, a reset link has been sent.');
        $this->redirect('login');
    }

    public function logout(): void {
        Auth::logout();
        setFlash('success', 'You have been logged out.');
        $this->redirect('login');
    }
}
