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

    public function bookSeat(): void {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request. Please refresh and try again.'], 422);
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'Name')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('phone', 'Phone')
            ->required('activity_title', 'Activity')
            ->required('seats', 'Seats');

        if (!$validator->passes()) {
            $this->json(['success' => false, 'message' => $validator->firstError()], 422);
        }

        $seats = max(1, min(20, (int)($_POST['seats'] ?? 1)));
        $activityTitle = trim($_POST['activity_title'] ?? '');
        $activityDate = trim($_POST['activity_date'] ?? '');
        $message = trim($_POST['message'] ?? '');

        require_once APP_PATH . '/Models/Notification.php';
        require_once APP_PATH . '/Models/User.php';

        try {
            $db = Database::getInstance();
            $db->exec(
                "CREATE TABLE IF NOT EXISTS activity_bookings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    activity_title VARCHAR(180) NOT NULL,
                    activity_date VARCHAR(40) NULL,
                    name VARCHAR(120) NOT NULL,
                    email VARCHAR(190) NOT NULL,
                    phone VARCHAR(50) NOT NULL,
                    seats INT NOT NULL DEFAULT 1,
                    message TEXT NULL,
                    user_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_activity_title (activity_title),
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            $stmt = $db->prepare(
                "INSERT INTO activity_bookings (activity_title, activity_date, name, email, phone, seats, message, user_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $activityTitle,
                $activityDate !== '' ? $activityDate : null,
                trim($_POST['name'] ?? ''),
                trim($_POST['email'] ?? ''),
                trim($_POST['phone'] ?? ''),
                $seats,
                $message !== '' ? $message : null,
                Auth::check() ? (int)Auth::id() : null,
            ]);
            $bookingId = (int)$db->lastInsertId();

            foreach (User::all() as $user) {
                if (($user['role'] ?? '') !== 'admin') {
                    continue;
                }
                Notification::create((int)$user['id'], 'New activity booking: ' . $activityTitle . ' (' . $seats . ' seat' . ($seats > 1 ? 's' : '') . ')', 'booking');
            }

            $this->json([
                'success' => true,
                'message' => 'Your seat has been booked successfully.',
                'booking_id' => $bookingId,
            ]);
        } catch (Throwable $e) {
            error_log('Activity booking failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Unable to complete your booking right now.'], 500);
        }
    }
}

