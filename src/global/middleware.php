<?php
if (isset($_SESSION['userId'])) // If session data for the user exists, i.e., the user is logged in
{
     return true;
}
else // Session data for the user does not exists, i.e., the user is not logged in
{
     header("LOCATION:".BASE_URL);
     exit;
}
?>