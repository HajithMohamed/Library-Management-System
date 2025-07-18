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
include DIR_URL.'src/global/updateFines.php'; // Updating all the fine amounts in the transactions table whenever someone visits this page

$message="";
$rows="";

$sql="SELECT * FROM transactions JOIN books ON transactions.isbn = books.isbn WHERE transactions.userId = '$userId' ORDER BY transactions.borrowDate ASC";
$result=$conn->query($sql);
if($result->num_rows>0) // Checking if the user has borrowed any books
{
  $index=1;
  while ($row = $result->fetch_assoc()) 
  {
    $hasFine = (int)$row["fine"] > 0;
    $rows .= "<tr>
      <td data-label='Sl.No.'>" . $index . "</td>
      <td data-label='ISBN No.'>" . htmlspecialchars($row["isbn"]) . "</td>
      <td data-label='Book Name'>" . htmlspecialchars($row["bookName"]) . "</td>
      <td data-label='Author Name'>" . htmlspecialchars($row["authorName"]) . "</td>
      <td data-label='Publisher Name'>" . htmlspecialchars($row["publisherName"]) . "</td>
      <td data-label='Borrow date'>" . date("d/m/Y", strtotime($row["borrowDate"])). "</td>
      <td data-label='Return Date'>" . date("d/m/Y", strtotime($row["returnDate"])) . "</td>
      <td data-label='Fine due'> Rs. " . htmlspecialchars($row["fine"]) . "</td>
      <td data-label='Actions'>
      <div class='action-buttons'>
      <form method='POST' onsubmit='return confirmReturn()'>
      <input type='hidden' name='tid' value='" . $row["tid"] . "'>
      <input type='hidden' name='isbn' value='" . $row["isbn"] . "'>
      <button type='submit' name='return' class='green-btn' " . ($hasFine ? "disabled" : "") . "><i class='fa-solid fa-book'></i>&nbsp;&nbsp;Return Book</button>
      </form>     
      <button class='red-btn' " . (!$hasFine ? "disabled" : "") . " onclick=\"openPayFine('".$row["tid"]."')\"><i class='fa-solid fa-indian-rupee-sign'></i>&nbsp;&nbsp;Pay Fine</button>
      </div>
      </td>
      </tr>";
    $index++;
  }
}
else// the user has not borrowed any books
{
  $message="<tr><td colspan='9'><center>You have borrowed no books...</center></td></tr>";
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["return"]) && isset($_POST["tid"]) && isset($_POST["isbn"])) // Triggerred when the user clicks 'Return Book' button for any book
{
    $tid = $_POST["tid"];
    $isbn= $_POST["isbn"];
    $sql1="DELETE FROM transactions WHERE tid = '$tid'";
    $sql2="UPDATE books SET available=available+1, borrowed=borrowed-1 WHERE isbn = '$isbn' ";
    if ( $conn->query($sql1)===TRUE && $conn->query($sql2)===TRUE ) 
    {
        echo "<script>
            alert('Book returned successfully.');
            window.location.href = 'userDashboard.php';
        </script>";
        exit();
    } 
    else 
    {
        echo "<script>alert('Error returning book.');</script>";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Books</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/tableLayoutWithSearch.css" />
<script>
// Confirmation prompt when 'Return Book' button is clicked for any book
function confirmReturn() 
{
return confirm("Are you sure you want to return this book?");
}
// Open Pay Fine page for a particular book when it's 'Pay Fine' button is clicked
function openPayFine(tid) 
{
    window.open('PayFine.php?tid=' + encodeURIComponent(tid), '_blank');
}
</script>
</head>
<body>

<div class="background-container"></div>

    <main class="content-wrapper">
    <h1> Your Books</h1>

    <!-- Table Display -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Sl.No</th>
                    <th>ISBN No.</th>
                    <th>Book Name</th>
                    <th>Author Name</th>
                    <th>Publisher Name</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Fine due</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="Results">
                <?=$message?>
                <?=$rows?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
