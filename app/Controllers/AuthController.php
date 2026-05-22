<?php

class AuthController extends Controller {

    private function getFullUrl(string $relativePath): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . $host . $relativePath;
    }

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
            setFlash('success', 'Signed in successfully.');
            try {
                require_once APP_PATH . '/Models/Notification.php';
                $adminId = 1;
                $name = Auth::user()['name'] ?? 'Unknown User';
                Notification::create(
                    $adminId,
                    "🔑 User logged in: {$name} has accessed their account.",
                    'admin_alert'
                );
            } catch (Throwable $e) {
                error_log('Failed to send login notification: ' . $e->getMessage());
            }
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
            setFlash('success', 'Account created successfully. Please sign in.');
            try {
                require_once APP_PATH . '/Models/Notification.php';
                $adminId = 1;
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
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/forgot', [
            'title'  => 'Reset Password',
            'layout' => 'auth',
        ]);
    }

    public function forgot(): void {
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('forgot-password');
        }

        require_once APP_PATH . '/Models/User.php';

        $validator = new Validator($_POST);
        $validator->required('email', 'Email')
                  ->email('email', 'Email');

        if (!$validator->passes()) {
            setFlash('error', $validator->firstError());
            $this->redirect('forgot-password');
        }

        $email = trim($_POST['email'] ?? '');
        $user = User::findByEmail($email);
        $dumpFile = ROOT_PATH . '/mail_dump.txt';
        $simulatedLink = null;

        if ($user) {
            try {
                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                if (!User::storeResetToken((int)$user['id'], $tokenHash, $expiresAt)) {
                    throw new RuntimeException('Unable to store reset token.');
                }

                $simulatedLink = url('reset-password') . '?token=' . urlencode($rawToken);
                $dumpEntry = implode(PHP_EOL, [
                    '========================================',
                    'Email: ' . $email,
                    'Name: ' . ($user['name'] ?? 'Unknown'),
                    'Generated At: ' . date('Y-m-d H:i:s'),
                    'Expires At: ' . $expiresAt,
                    'Reset Link: ' . $this->getFullUrl($simulatedLink),
                    ''
                ]) . PHP_EOL;

                if (file_put_contents($dumpFile, $dumpEntry, FILE_APPEND | LOCK_EX) === false) {
                    throw new RuntimeException('Unable to write simulated reset link to mail_dump.txt.');
                }
            } catch (Throwable $e) {
                error_log('Password reset simulation failed: ' . $e->getMessage());
                setFlash('error', 'We could not generate the simulated reset link. Please try again.');
                $this->redirect('forgot-password');
            }
        }

        $this->view('auth/forgot', [
            'title' => 'Reset Password',
            'layout' => 'auth',
            'resetSent' => true,
            'resetNotice' => 'If that email exists, the reset link has been simulated and saved to mail_dump.txt.',
            'simulatedLink' => $simulatedLink,
            'dumpFile' => 'mail_dump.txt',
        ]);
    }

    public function resetForm(): void {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            $this->view('auth/reset_view', [
                'title' => 'Reset Password',
                'layout' => 'auth',
                'isValid' => false,
                'errorMessage' => 'Invalid reset link. Please request a new one.',
            ]);
            return;
        }

        require_once APP_PATH . '/Models/User.php';

        $tokenHash = hash('sha256', $token);
        $resetUser = User::findByResetTokenHash($tokenHash);
        if (!$resetUser) {
            $this->view('auth/reset_view', [
                'title' => 'Reset Password',
                'layout' => 'auth',
                'isValid' => false,
                'errorMessage' => 'Invalid or expired reset link. Please request a new one.',
            ]);
            return;
        }

        $this->view('auth/reset_view', [
            'title' => 'Reset Password',
            'layout' => 'auth',
            'isValid' => true,
            'token' => $token,
            'userName' => $resetUser['name'] ?? 'User',
        ]);
    }

    public function reset(): void {
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('forgot-password');
        }

        require_once APP_PATH . '/Models/User.php';

        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($token === '') {
            setFlash('error', 'Invalid reset token.');
            $this->redirect('forgot-password');
        }

        $validator = new Validator($_POST);
        $validator->required('password', 'Password')
                  ->minLength('password', 6, 'Password')
                  ->matches('password', 'password_confirmation', 'Password');

        if (!$validator->passes()) {
            setFlash('error', $validator->firstError());
            $this->redirect('reset-password?token=' . urlencode($token));
        }

        $tokenHash = hash('sha256', $token);
        $resetUser = User::findByResetTokenHash($tokenHash);
        if (!$resetUser) {
            $this->view('auth/reset_view', [
                'title' => 'Reset Password',
                'layout' => 'auth',
                'isValid' => false,
                'errorMessage' => 'Invalid or expired reset link. Please request a new one.',
            ]);
            return;
        }

        $userId = (int)$resetUser['id'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "UPDATE users
                 SET password = ?, reset_token_hash = NULL, reset_token_expires = NULL
                 WHERE id = ? AND reset_token_hash = ? AND reset_token_expires > NOW()"
            );
            $stmt->execute([$hashedPassword, $userId, $tokenHash]);

            if ($stmt->rowCount() < 1) {
                $this->view('auth/reset_view', [
                    'title' => 'Reset Password',
                    'layout' => 'auth',
                    'isValid' => false,
                    'errorMessage' => 'This reset link is no longer valid. Please request a new one.',
                ]);
                return;
            }

            setFlash('success', 'Your password has been reset successfully. Please sign in with your new password.');
            $this->redirect('login');
        } catch (Throwable $e) {
            error_log('Password reset failed: ' . $e->getMessage());
            $this->view('auth/reset_view', [
                'title' => 'Reset Password',
                'layout' => 'auth',
                'isValid' => false,
                'errorMessage' => 'Failed to reset password. Please try again.',
            ]);
        }
    }

    public function logout(): void {
        Auth::logout();
        setFlash('success', 'You have been logged out.');
        $this->redirect('login');
    }
}
