<?php
include '../../config/config.php';
include DIR_URL.'config/dbConnection.php';
session_start();

$message = '';

// Support link-based GET verification as: verifyOtp.php?userId=...&token=...
if($_SERVER["REQUEST_METHOD"]==="GET" && isset($_GET['userId']) && isset($_GET['token']))
{
  $userId = $_GET['userId'];
  $otp = $_GET['token'];

  $sql = "SELECT otp, otpExpiry, isVerified FROM users WHERE userId='$userId'";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0)
  {
    $row = $result->fetch_assoc();
    if (intval($row['isVerified']) === 1)
    {
      echo "<script>alert('Account already verified.'); window.location.href='".BASE_URL."';</script>";
      exit;
    }
    $now = strtotime('now');
    $expiry = strtotime($row['otpExpiry']);
    if ($now > $expiry)
    {
      echo "<script>alert('OTP expired. Please signup again.'); window.location.href='".BASE_URL."';</script>";
      exit;
    }
    if ($otp === $row['otp'])
    {
      $upd = "UPDATE users SET isVerified=1, otp=NULL, otpExpiry=NULL WHERE userId='$userId'";
      if ($conn->query($upd) === TRUE)
      {
        echo "<script>alert('Verification successful. You can now login.'); window.location.href='".BASE_URL."';</script>";
        exit;
      }
    }
  }
  // Fallback to form with error
  $message = 'Invalid verification link.';
}
else if($_SERVER["REQUEST_METHOD"]==="POST")
{
  $userId = $_POST['userId'];
  $otp = $_POST['otp'];

  $sql = "SELECT otp, otpExpiry, isVerified FROM users WHERE userId='$userId'";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0)
  {
    $row = $result->fetch_assoc();
    if (intval($row['isVerified']) === 1)
    {
      $message = 'Account already verified.';
    }
    else if (empty($row['otp']) || empty($row['otpExpiry']))
    {
      $message = 'No OTP found. Please signup again.';
    }
    else
    {
      $now = strtotime('now');
      $expiry = strtotime($row['otpExpiry']);
      if ($now > $expiry)
      {
        $message = 'OTP expired. Please signup again.';
      }
      else if ($otp !== $row['otp'])
      {
        $message = 'Invalid OTP.';
      }
      else
      {
        // Mark verified and clear OTP fields
        $upd = "UPDATE users SET isVerified=1, otp=NULL, otpExpiry=NULL WHERE userId='$userId'";
        if ($conn->query($upd) === TRUE)
        {
          echo "<script>alert('Verification successful. You can now login.'); window.location.href='".BASE_URL."';</script>";
          exit;
        }
        else
        {
          $message = 'Failed to update verification status.';
        }
      }
    }
  }
  else
  {
    $message = 'User not found.';
  }
}
else
{
  $userId = isset($_GET['userId']) ? $_GET['userId'] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/formLayout.css" />
</head>
<body>
  <div class="container">
    <h2>Verify your account</h2>
    <form method="POST">
      <input type="hidden" name="userId" value="<?php echo htmlspecialchars($userId); ?>">
      <div class="input-group">
        <label for="otp"><b>Enter OTP</b></label>
        <input type="text" id="otp" name="otp" required>
        <span class="error"><?php echo htmlspecialchars($message); ?></span>
      </div>
      <button type="submit">Verify</button>
    </form>
  </div>
</body>
</html>


