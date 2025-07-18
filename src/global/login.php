<?php
$userId=$_POST['userId'];
$password=$_POST['password'];
$userType=$_POST['userType'];

$sql="SELECT * FROM users WHERE userId='$userId'";
$result=$conn->query($sql);

if ($result->num_rows > 0) // userId matched ,i.e, user with the given userId exists
{
     $user = $result->fetch_assoc();

     $hashed_password = $user['password'];//retrieving user's hashed password stored in database
     $db_userType = $user['userType'];//retrieving user's userType stored in database
          
     $isPasswordCorrect = password_verify($password, $hashed_password);
     $isUserTypeCorrect = ($db_userType == $userType);

     if ($isPasswordCorrect && $isUserTypeCorrect) // password and userType entered are correct
     {
          // REDIRECTING UPON VALID CREDENTIALS

          // Creating session variables on successful Login
          $_SESSION['userId'] = $userId;
          $_SESSION['userType'] = $userType;

          if ($userType == "Faculty" || $userType == "Student") 
          {
               echo '<script>alert("Login Successful, Welcome back '. htmlspecialchars($userId, ENT_QUOTES) .'");</script>';
               echo '<script>window.location.href="'.BASE_URL.'src/user/userDashboard.php";</script>';
          } 
          else if ($userType == "Admin") 
          {
               echo '<script>alert("Login Successful, Welcome back '. htmlspecialchars($userId, ENT_QUOTES) .'");</script>';
               echo '<script>window.location.href="'.BASE_URL.'src/admin/adminDashboard.php";</script>';
          }
          exit();
     } 
     else //password or userType or both are incorrect
     {
          if (!$isPasswordCorrect) 
          {
               $passwordError = "Wrong Password entered<br>";
          }
          if (!$isUserTypeCorrect)
          {
               $userTypeError = "Wrong Usertype selected<br>";
          }
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