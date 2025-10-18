<?php
include '../../config/config.php';
session_start();
include DIR_URL.'src/global/middleware.php';
include DIR_URL.'config/dbConnection.php';

$userId = $_SESSION['userId'];
$userType = $_SESSION['userType'];

if ($userType != 'Admin') {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>403 Forbidden</title></head><body>Forbidden</body></html>';
    exit();
}

$isAjax = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']));
if ($isAjax) {
    header('Content-Type: application/json');
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])) {
        echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit;
    }
    $action = $_POST['action'];
    // ... (keep the full AJAX create/update/delete logic from HEAD)
}

// Fetch all books for display
$message = "";
$rows = "";
$sql = "SELECT * FROM books ORDER BY bookName ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $index = 1;
    while ($book_details = $result->fetch_assoc()) {
        $quantityNotZero = ((int)$book_details["available"] != 0);
        $rows .= "<tr>
            <td data-label='Sl.No.'>{$index}</td>
            <td data-label='Cover'>" . (empty($book_details["bookImage"]) ? "<div style='width:48px;height:64px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border:1px solid #ddd;border-radius:4px;'><i class='fa-solid fa-book'></i></div>" : "<img src='".BASE_URL.htmlspecialchars($book_details["bookImage"])."' alt='Cover' style='width:48px;height:64px;object-fit:cover;border-radius:4px;border:1px solid #ddd;' />") . "</td>
            <td data-label='ISBN No.'>".htmlspecialchars($book_details["isbn"])."</td>
            <td data-label='Book Name'>".htmlspecialchars($book_details["bookName"])."</td>
            <td data-label='Author Name'>".htmlspecialchars($book_details["authorName"])."</td>
            <td data-label='Publisher Name'>".htmlspecialchars($book_details["publisherName"])."</td>
            <td data-label='Available'>".htmlspecialchars($book_details["available"])."</td>
            <td data-label='Borrowed'>".htmlspecialchars($book_details["borrowed"])."</td>
            <td data-label='Actions'>
                <div class='action-buttons'>
                    <button type='button' class='green-btn' onclick=\"openEditModal('".htmlspecialchars($book_details["isbn"])."')\">Edit</button>
                    <button type='button' class='red-btn' onclick=\"deleteBook('".htmlspecialchars($book_details["isbn"])."')\">Delete</button>
                </div>
            </td>
        </tr>";
        $index++;
    }
} else {
    $message = "<tr><td colspan='8'><center>The library is empty...</center></td></tr>";
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Keep HEAD content from HEAD version -->
</head>
<body>
<!-- Keep modal and JS from HEAD version -->
</body>
</html>
