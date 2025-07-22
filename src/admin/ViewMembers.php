<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
include DIR_URL.'config/dbConnection.php';
$userId = $_SESSION['userId'];//Fetching userId and userType from session data
$userType = $_SESSION['userType'];
if ($userType != 'Admin') // if an invalid userType tries to access this page
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

$message="";
$rows = "";
//By default, all users of the library are shown
$sql="SELECT * FROM users ORDER BY userType ASC,userId ASC";
$result=$conn->query($sql);
if($result->num_rows>0)//check if there are any members in the library
{
  $index=1;
  while ($row = $result->fetch_assoc()) 
  {
    $rows .= "<tr>
        <td data-label='Sl.No.'>" . $index . "</td>
        <td data-label='User Type'>" . htmlspecialchars($row["userType"]) . "</td>
        <td data-label='User Id'>" . htmlspecialchars($row["userId"]) . "</td>
        <td data-label='Gender'>" . htmlspecialchars($row["gender"]) . "</td>
        <td data-label='Date of Birth'>" . (empty($row["dob"]) ? "N/A" : htmlspecialchars($row["dob"])) . "</td>
        <td data-label='Phone Number'>" . htmlspecialchars($row["phoneNumber"]) . "</td>
        <td data-label='Email Id'>" . htmlspecialchars($row["emailId"]) . "</td>
        <td data-label='Address'>" . (empty($row["address"]) ? "N/A" : htmlspecialchars($row["address"])) . "</td>
      </tr>";
    $index++;
   }
}
else//No users present in the library
{
     $message="<tr><td colspan='8'><center>There are no users registered yet...</center></td></tr>";
}
$conn->close();
?>
<!DOCTYPE html>  
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Members</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/tableLayoutWithSearch.css" />
  <script>
  // Using AJAX for real time search
  function searchMembers() 
  {
    const query=document.getElementById('search').value;
    const xhr=new XMLHttpRequest();
    xhr.open('GET','search_ViewMembers.php?query='+query,true);
    xhr.onreadystatechange=function()
    {
      if(xhr.readyState===4 && xhr.status===200)
      {
        document.getElementById('searchResults').innerHTML=xhr.responseText;
      }
    };
    xhr.send();
  }
  </script>
</head>
<body>

<div class="background-container"></div>

 <main class="content-wrapper">
    <h1> View Members</h1>
    <!-- Search Bar -->
  <div class="search-container">

      <input type="text" id="search" name="search" placeholder="Search here..." required 
      onkeyup="searchMembers()"/>

      <input type="button" id="search-button" value="Search" onclick="searchMembers()"/>
    </div>

  <!-- Table Display -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Sl.No.</th>
          <th>User Type</th>
          <th>User Id</th>
          <th>Gender</th>
          <th>Date of Birth</th>
          <th>Phone Number</th>
          <th>Email Id</th>
          <th>Address</th>
        </tr>
      </thead>
      <tbody id="searchResults">
        <?=$message?>
        <?=$rows?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
