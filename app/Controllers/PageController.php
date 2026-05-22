<?php

class PageController extends Controller {

    public function about(): void {
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/User.php';
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Review.php';

        $this->view('pages/about', [
            'title' => 'About Us',
            'stats' => [
                'books' => Book::count(),
                'members' => User::count(),
                'borrows' => Borrow::count(),
                'reviews' => Review::count(),
            ],
            'layout' => 'public',
        ]);
    }

    public function contact(): void {
        $this->view('pages/contact', [
            'title' => 'Contact Us',
            'layout' => 'public',
        ]);
    }

    public function submitContact(): void {
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request. Please refresh and try again.');
            $this->redirect('contact');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'Name')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('subject', 'Subject')
            ->required('message', 'Message')
            ->minLength('message', 10, 'Message');

        if (!$validator->passes()) {
            setFlash('error', $validator->firstError());
            $this->redirect('contact');
        }

        require_once APP_PATH . '/Models/ContactMessage.php';
        ContactMessage::create($_POST);

        try {
            require_once APP_PATH . '/Models/Notification.php';
            require_once APP_PATH . '/Models/User.php';
            foreach (User::all() as $user) {
                if (($user['role'] ?? '') !== 'admin') {
                    continue;
                }
                Notification::create((int)$user['id'], 'New contact message: ' . trim($_POST['subject'] ?? ''), 'admin_alert');
            }
        } catch (Throwable $e) {
            error_log('Contact notification failed: ' . $e->getMessage());
        }

        setFlash('success', 'Thanks for contacting us. We will get back to you soon.');
        $this->redirect('contact');
    }
}

