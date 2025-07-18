<?php
if (isset($_SESSION['userId']))
{
     $userId=$_SESSION['userId'];
     $userType=$_SESSION['userType'];
     
     if ($userType=="Admin") // if Admin tries to access login or signup page while already logged in, then redirect him/her to adminIndex
     {
          header("LOCATION:".BASE_URL."src/admin/adminDashboard.php");
     }
     else if ($userType=="Faculty" || $userType=="Student") // if Faculty/Student tries to access login or signup page while already logged in, then redirect him/her to userIndex
     {
          header("LOCATION:".BASE_URL."src/user/userDashboard.php");
     }
     else // if somehow session data is corrupted
     {
          http_response_code(401);
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
     }
     exit;
}
?>