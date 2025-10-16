<?php
include '../../config/config.php';
include DIR_URL.'config/dbConnection.php';
session_start();

// Only admins can delete
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Admin')
{
  http_response_code(403);
  exit('Forbidden');
}

$currentAdmin = $_SESSION['userId'];
$targetUser = isset($_POST['userId']) ? $_POST['userId'] : '';

if (empty($targetUser))
{
  header('Location: '.BASE_URL.'src/admin/ViewMembers.php');
  exit();
}

// Prevent self-deletion if target is an Admin and same as current admin
$sql = "SELECT userType FROM users WHERE userId='$targetUser'";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0)
{
  $row = $res->fetch_assoc();
  if ($row['userType'] === 'Admin' && $targetUser === $currentAdmin)
  {
    echo "<script>alert('You cannot delete your own admin account.'); window.location.href='".BASE_URL."src/admin/ViewMembers.php';</script>";
    exit();
  }
}

// Proceed to delete
$del = "DELETE FROM users WHERE userId='$targetUser'";
if ($conn->query($del) === TRUE)
{
  echo "<script>alert('User deleted successfully'); window.location.href='".BASE_URL."src/admin/ViewMembers.php';</script>";
}
else
{
  echo "<script>alert('Failed to delete user'); window.location.href='".BASE_URL."src/admin/ViewMembers.php';</script>";
}

?>


