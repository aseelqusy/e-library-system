<?php

class ReportController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function export(): void {
        // جلب نوع التقرير والصيغة من الرابط
        $type = $_GET['type'] ?? '';
        $format = $_GET['format'] ?? 'csv';

        if ($format !== 'csv') {
            $this->json(['success' => false, 'message' => 'Only CSV export is supported currently.'], 400);
            return;
        }

        // الحصول على اتصال قاعدة البيانات الحية
        $db = Database::getInstance();

        // فحص نوع التقرير المطلوب وتنفيذ الدالة المناسبة له
        // فحص نوع التقرير المطلوب بناءً على الكلمات المرسلة حقيقياً من أزرار واجهتك
        switch ($type) {
            case 'borrows':
            case 'popular':
                $this->exportPopularBooks($db);
                break;

            case 'inventory':
            case 'overdue':
                $this->exportOverdueBorrows($db);
                break;

            case 'users':
            case 'monthly':
                $this->exportMonthlySummary($db);
                break;

            default:
                $this->json(['success' => false, 'message' => 'Invalid report type. Received: ' . $type], 400);
        }
    }

    private function exportPopularBooks($db): void {
        $query = "SELECT b.id as book_id, b.title, b.author, b.isbn, COUNT(br.id) as total_borrows 
                  FROM books b 
                  LEFT JOIN borrows br ON b.id = br.book_id 
                  GROUP BY b.id 
                  ORDER BY total_borrows DESC";

        $stmt = $db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "popular_books_" . date('Y-m-d') . ".csv";
        $headers = ['Book ID', 'Title', 'Author', 'ISBN', 'Total Borrows'];

        $this->downloadCSV($filename, $headers, $data);
    }

    private function exportOverdueBorrows($db): void {
        $today = date('Y-m-d');
        $query = "SELECT br.id as borrow_id, b.title as book_title, u.name as user_name, 
                         br.borrow_date, br.due_date, DATEDIFF(:today, br.due_date) as days_late
                  FROM borrows br
                  JOIN books b ON br.book_id = b.id
                  JOIN users u ON br.user_id = u.id
                  WHERE br.status = 'active' AND br.due_date < :today
                  ORDER BY days_late DESC";

        // تعديل تم هنا باستخدام prepare بدلاً من query لتجنب الـ Fatal Error السابق
        $stmt = $db->prepare($query);
        $stmt->execute(['today' => $today]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "overdue_report_" . date('Y-m-d') . ".csv";
        $headers = ['Borrow ID', 'Book Title', 'User Name', 'Borrow Date', 'Due Date', 'Days Overdue'];

        $this->downloadCSV($filename, $headers, $data);
    }

    private function exportMonthlySummary($db): void {
        $query = "SELECT DATE_FORMAT(borrow_date, '%Y-%m') as month, COUNT(id) as total_borrows,
                         SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_count,
                         SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count
                  FROM borrows 
                  GROUP BY month 
                  ORDER BY month DESC";

        $stmt = $db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "monthly_summary_" . date('Y-m-d') . ".csv";
        $headers = ['Month', 'Total Operations', 'Returned Count', 'Active Count'];

        $this->downloadCSV($filename, $headers, $data);
    }

    private function downloadCSV(string $filename, array $headers, array $data): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $output = fopen('php://output', 'w');

        // لدعم اللغة العربية في ملف الاكسل
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, $headers);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }
}