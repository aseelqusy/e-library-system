<?php

class DashboardController {
    public function index() {
        // Get authenticated user
        $user = Auth::user();

        // Fetch user statistics from database
        $data = [
            'title' => 'My Dashboard',
            'isNewUser' => $this->isNewUser($user['id']),
            'user_avatar' => $user['avatar'] ?? null,
            'member_since' => $user['created_at'] ?? null,

            // Statistics
            'total_borrowed' => $this->getUserBorrowCount($user['id']),
            'active_loans' => $this->getActiveLoanCount($user['id']),
            'wishlist_count' => $this->getWishlistCount($user['id']),
            'pending_orders' => $this->getPendingOrderCount($user['id']),

            // Activity
            'recent_activity' => $this->getRecentActivity($user['id']),

            // Current page for active state
            'current_page' => 'dashboard'
        ];

        return view('dashboard', $data);
    }

    private function getUserBorrowCount($userId) {
        // Query total borrows from database
        return DB::table('borrows')
            ->where('user_id', $userId)
            ->count();
    }

    private function getActiveLoanCount($userId) {
        // Query active loans (not returned)
        return DB::table('borrows')
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->count();
    }

    private function getWishlistCount($userId) {
        // Query wishlist items
        return DB::table('wishlist')
            ->where('user_id', $userId)
            ->count();
    }

    private function getPendingOrderCount($userId) {
        // Query pending orders
        return DB::table('orders')
            ->where('user_id', $userId)
            ->where('status', '!=', 'completed')
            ->count();
    }

    private function getRecentActivity($userId, $limit = 5) {
        // Get recent activity (borrows, returns, orders)
        $activities = [];

        // Recent borrows
        $borrows = DB::table('borrows as b')
            ->join('books as bk', 'b.book_id', '=', 'bk.id')
            ->where('b.user_id', $userId)
            ->select('b.created_at as created_at', 'bk.title', DB::raw("'borrow' as type"))
            ->limit(5)
            ->get();

        foreach ($borrows as $borrow) {
            $activities[] = [
                'type' => 'borrow',
                'icon' => 'book',
                'action' => 'Borrowed "' . $borrow->title . '"',
                'time' => $this->formatTime($borrow->created_at)
            ];
        }

        // Recent orders
        $orders = DB::table('orders as o')
            ->where('o.user_id', $userId)
            ->select('o.created_at', 'o.status', DB::raw("'order' as type"))
            ->limit(5)
            ->get();

        foreach ($orders as $order) {
            $activities[] = [
                'type' => 'order',
                'icon' => 'box',
                'action' => 'Order ' . ucfirst($order->status),
                'time' => $this->formatTime($order->created_at)
            ];
        }

        // Sort by date (newest first)
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, $limit);
    }

    private function isNewUser($userId) {
        $user = DB::table('users')->find($userId);
        $createdDate = new DateTime($user->created_at);
        $today = new DateTime();
        $interval = $today->diff($createdDate);

        return $interval->days < 7; // User is new if created in last 7 days
    }

    private function formatTime($datetime) {
        $dt = new DateTime($datetime);
        $now = new DateTime();
        $interval = $now->diff($dt);

        if ($interval->days > 0) {
            return $interval->days . 'd ago';
        } elseif ($interval->h > 0) {
            return $interval->h . 'h ago';
        } elseif ($interval->i > 0) {
            return $interval->i . 'm ago';
        } else {
            return 'Just now';
        }
    }
}