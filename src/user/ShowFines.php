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
include DIR_URL.'src/global/updateFines.php';

$message="";
$rows="";

$sql="SELECT * FROM transactions JOIN books ON transactions.isbn = books.isbn WHERE transactions.userId = '$userId' AND transactions.fine > 0 ORDER BY transactions.borrowDate ASC";
$result=$conn->query($sql);
if($result->num_rows>0) // Checking if the user has any fines due
{
  $index=1;
  while ($row = $result->fetch_assoc()) 
  {
    $rows .= "<tr>
      <td data-label='Sl.No.'>" . $index . "</td>
      <td data-label='ISBN No.'>" . htmlspecialchars($row["isbn"]) . "</td>
      <td data-label='Book Name'>" . htmlspecialchars($row["bookName"]) . "</td>
      <td data-label='Author Name'>" . htmlspecialchars($row["authorName"]) . "</td>
      <td data-label='Publisher Name'>" . htmlspecialchars($row["publisherName"]) . "</td>
      <td data-label='Borrow Date'>" . date("d/m/Y", strtotime($row["borrowDate"])). "</td>
      <td data-label='Return Date'>" . date("d/m/Y", strtotime($row["returnDate"])) . "</td>
      <td data-label='Fine due'> Rs. " . htmlspecialchars($row["fine"]) . "</td>
      <td data-label='Fine last payed on'>" . (empty($row["lastFinePaymentDate"]) ? "N/A" : date("d/m/Y", strtotime($row["lastFinePaymentDate"]))) . "</td>
      <td data-label='Actions'>
      <div class='action-buttons'>
        <button class='red-btn' onclick=\"openPayFine('".$row["tid"]."')\"><i class='fa-solid fa-indian-rupee-sign'></i>&nbsp;&nbsp;Pay Fine</button>        
      </div>
      </td>
      </tr>";
    $index++;
  }
}
else// the user has no pending fines due
{
  $message="<tr><td colspan='10'><center>No fines due...</center></td></tr>";
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Fines</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/tableLayoutWithSearch.css" />
<script>
// Opening Pay Fine page for a particular book when it's 'Pay Fine' button is clicked
function openPayFine(tid) 
{
    window.open('PayFine.php?tid=' + encodeURIComponent(tid), '_blank');
}
</script>
</head>
<body>

<div class="background-container"></div>

    <main class="content-wrapper">
    <h1> Fines Due</h1>

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
                    <th>Fine last payed on</th>
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
