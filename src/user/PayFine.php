<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
include DIR_URL.'config/dbConnection.php';
$userId = $_SESSION['userId'];//Fetching userId and userType from session data
$userType = $_SESSION['userType'];
if ($userType != 'Faculty' && $userType != 'Student') // if an invalid userType tries to access this page
{
  http_response_code(403);
  echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background: url("../../assets/images/http403.jpg") no-repeat center center;
            background-size: contain;
            background-color: black;
        }
    </style>
</head>
<body>
</body>
</html>';
  exit();
}

$tid = $_GET['tid'] ?? null;

if ($tid) // Checking if any tid is passed in the query, i.e., this page was called by a Pay Fine button either from ReturnBooks.php or ShowFines.php
{
    $sql = "SELECT * FROM transactions JOIN books ON transactions.isbn = books.isbn WHERE tid = '$tid' AND userId = '$userId' AND fine > 0";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) // A valid transaction with a fine was passed in the query
    {
        $row = $result->fetch_assoc();
    } 
    else // An invalid transaction was passed in the query
    {
        echo "<script>
            alert('Invalid or already paid transaction.');
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                    window.close();
                } else {
                    window.location.href = 'userDashboard.php';
                }
            }, 100);
        </script>";
        exit();
    }
} 
else // No tid was passed in the query
{
    echo "<script>
            alert('No transaction selected.');
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                    window.close();
                } else {
                    window.location.href = 'userDashboard.php';
                }
            }, 100);
        </script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payFine']) && isset($_POST['tid'])) // Triggered when Pay Fine button was clicked in this page and then 'ok' was selected in the confirmation prompt
{
    $tid = $_POST['tid'];
    $today = date('Y-m-d');
    $update = "UPDATE transactions SET fine = 0, lastFinePaymentDate = '$today' WHERE tid = '$tid'";
    if ($conn->query($update) === TRUE) // Fine payment was successful
    {
        echo "<script>
            alert('Fine paid successfully.');
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                    window.close();
                } else {
                    window.location.href = 'userDashboard.php';
                }
            }, 100);
        </script>";
        exit();
    } 
    else // Fine payment was not successful
    {
        echo "<script>
            alert('Error processing payment.');
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                    window.close();
                } else {
                    window.location.href = 'userDashboard.php';
                }
            }, 100);
        </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Fine</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/payFine.css" />
    <script>
        // Confirmation prompt for Paying fine
        function confirmPayment() {
            const fine = <?= $row['fine'] ?>;
            if (confirm(`Are you sure you want to pay Rs. ${fine}?`)) {
                document.getElementById('payForm').submit();
            }
        }

        // Closing the tab or redirecting to userIndex.php when 'Cancel' button is clicked 
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("cancel-btn").onclick=function(){
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    window.close();
                } else {
                    window.location.href = 'userDashboard.php';
                }
            }, 100);
            };
        });
    </script>
</head>
<body>
<div class="background-container"></div>
<main class="modal-container">
    <div class="modal-content">
        <h2>Payment Details</h2>
        <div class="details">
            
            <!-- Inline items -->
            <div class="detail-item detail-item-inline">
                <strong>ISBN: </strong>
                <span><?= htmlspecialchars($row['isbn']) ?></span>
            </div>
            
            <!-- Stacked items: Value appears under the label -->
            <div class="detail-item detail-item-stacked">
                <strong>Book Name: </strong>
                <span><?= htmlspecialchars($row['bookName']) ?></span>
            </div>
            <div class="detail-item detail-item-stacked">
                <strong>Author Name: </strong>
                <span><?= htmlspecialchars($row['authorName']) ?></span>
            </div>
            <div class="detail-item detail-item-stacked">
                <strong>Publisher Name: </strong>
                <span><?= htmlspecialchars($row['publisherName']) ?></span>
            </div>

            <!-- Inline items -->
            <div class="detail-item detail-item-inline">
                <strong>Borrow Date: </strong>
                <span><?= date('d/m/Y', strtotime($row['borrowDate'])) ?></span>
            </div>
            <div class="detail-item detail-item-inline">
                <strong>Return Date: </strong>
                <span><?= date('d/m/Y', strtotime($row['returnDate'])) ?></span>
            </div>
            <div class="detail-item detail-item-inline detail-item-fine">
                <strong>Fine Due: </strong>
                <span>Rs. <?= $row['fine'] ?></span>
            </div>
        </div>
        <form id="payForm" method="POST">
            <input type="hidden" name="tid" value="<?= $row['tid'] ?>">
            <input type="hidden" name="payFine" value="1">
            <div class="button-container">
                <button type="button" id="pay-btn" class="pay-btn" onclick="confirmPayment()">Pay Fine</button>
                <button type="button" id="cancel-btn" class="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>