<?php
$userId=$_POST['userId'];
$password=$_POST['password'];

$sql="SELECT * FROM users WHERE userId='$userId'";
$result=$conn->query($sql);

if ($result->num_rows > 0) // userId matched ,i.e, user with the given userId exists
{
     $user = $result->fetch_assoc();

    $hashed_password = $user['password'];//retrieving user's hashed password stored in database
    $db_userType = $user['userType'];//retrieving user's userType stored in database
    $isVerified = isset($user['isVerified']) ? intval($user['isVerified']) : 1;
          
     $isPasswordCorrect = password_verify($password, $hashed_password);

    if ($isPasswordCorrect) // password is correct
     {
          if ($isVerified !== 1)
          {
               $passwordError = "Account not verified. Please check your email for the verification link.";
               session_unset();
               session_destroy();
               return;
          }
          // REDIRECTING UPON VALID CREDENTIALS

          // Creating session variables on successful Login
          $_SESSION['userId'] = $userId;
          $_SESSION['userType'] = $db_userType; // Use the userType from database

          if ($db_userType == "Faculty" || $db_userType == "Student") 
          {
               echo '<script>alert("Login Successful, Welcome back '. htmlspecialchars($userId, ENT_QUOTES) .'");</script>';
               echo '<script>window.location.href="'.BASE_URL.'src/user/userDashboard.php";</script>';
          } 
          else if ($db_userType == "Admin") 
          {
               echo '<script>alert("Login Successful, Welcome back '. htmlspecialchars($userId, ENT_QUOTES) .'");</script>';
               echo '<script>window.location.href="'.BASE_URL.'src/admin/adminDashboard.php";</script>';
          }
          exit();
     } 
     else //password is incorrect
     {
          $passwordError = "Wrong Password entered<br>";
          session_unset();
          session_destroy();
     }
} 
else //userId didn't match
{
     $userIdError = "There is no account with this User ID. Sign Up now<br>";
     session_unset();
     session_destroy();
}
?>