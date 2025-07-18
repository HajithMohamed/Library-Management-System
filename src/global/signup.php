<?php
include '../../config/config.php';
include DIR_URL.'config/createDB.php'; // create database and tables if not exists
include DIR_URL.'config/dbConnection.php'; // connect to database
session_start();
include DIR_URL.'src/global/redirect.php';// prevent access to this page by redirecting to respective dashboard if a session is active

$message="";

if($_SERVER["REQUEST_METHOD"]==="POST")
{
$userId=$_POST['userId'];
$password=$_POST['password'];
$userType=$_POST['userType'];
$gender=$_POST['gender'];
$dob=$_POST['dob'];
$emailId=$_POST['emailId'];
$phoneNumber=$_POST['phoneNumber'];
$address=$_POST['address'];

$sql="SELECT * FROM users WHERE userId='$userId'";
$result=$conn->query($sql);
if($result->num_rows>0) // An user with the entered userId already exists
{
    $message="This User Id is already taken. Please try to regsiter with some other User Id";
}
else // Signup successful
{
     $hashed_password=password_hash($password,PASSWORD_DEFAULT);// hashing the password
     $sql="INSERT INTO users (userId,password,userType,gender,dob,emailId,phoneNumber,address) 
     VALUES('$userId','$hashed_password','$userType','$gender','$dob','$emailId','$phoneNumber','$address')";
     if($conn->query($sql)===TRUE)
     {
          echo "<script>
            alert('Signup Successful');
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
                    window.close();
                } else {
                    window.location.href = '".BASE_URL."';
                }
            }, 100);
        </script>";
          exit;
     }
     else
     {
          echo "Error: ".$sql."<br>".$conn->error;
     }
}
$conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="An Integrated Library System (ILS) for managing book inventory, user authentication, student and faculty access, and admin-level controls. Efficient and responsive system for academic institutions.">
  <meta name="author" content="Debrup Chatterjee">
  <title>Sign Up - Integrated Library System</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/signup.css" />
  <script>
    //Password Eye Functionality
    function myFunction() 
    {
      var x = document.getElementById("password");
      var eye = document.querySelector('.toggle-password');
      if (x.type === "password") {
        x.type = "text";
        eye.innerHTML = '<i class="fa-solid fa-eye-slash"></i>'; // closed eye
      } else {
        x.type = "password";
        eye.innerHTML = '<i class="fa-solid fa-eye"></i>'; // open eye
      }
    }

    //Validation of Form inputs
    function validateForm()
    {
      let isValid = true;

      // Validate Phone Number
      const phoneNumber = document.getElementById("phoneNumber").value;
      const phoneNumberError = document.getElementById("phoneNumberError");
      if (!/^\d{10}$/.test(phoneNumber)) {
        phoneNumberError.textContent = "Phone number must have 10 digits";
        isValid = false;
      } else {
        phoneNumberError.textContent = "";
      }

      // Validate Password
      const password = document.getElementById("password").value;
      const passwordError = document.getElementById("passwordError");
      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
      if (!passwordRegex.test(password) || password.length<5) {
        passwordError.textContent = "Password must be atleast 5 characters long and contain at least one uppercase, one lowercase, and one number";
        isValid = false;
      } else {
        passwordError.textContent = "";
      }

      // Admin Code validation in case userType is Admin
      if (document.getElementById("userType").value==="Admin")
      {
          const code=prompt("Enter the Admin registration code");
          const admin_code="<?php echo ADMIN_CODE;?>";
          if (code===null || code.trim() === "")
          {
            isValid=false;
            alert("Please enter the admin registration code to register as an Admin !");
          }
          else if (code !== admin_code)
          {
            isValid=false;
            alert("Incorect Admin registration code entered !");
          }
      }

      return isValid;
    }

    //Remove error message function
    function initiate()
    {
      document.getElementById("userIdError").innerHTML="";
    }

    //Login Link 
    document.addEventListener("DOMContentLoaded", function () {
      document.getElementById("loginLink").onclick = function () {
        setTimeout(function() {
          if (window.opener && !window.opener.closed) // checking if this page was opened in a new tab and if so, then is the opening tab is still opened and not closed
          {
            window.close();
          } 
          else // if this page was opened by any other page in a new tab or if it was, then the opening tab has been closed
          {
            window.location.href = '<?php echo BASE_URL;?>';
          }
        }, 100);
      };
    });
    
</script>
</head>
<body>
  <div class="container">
    <h2>Create an Account</h2>
    <p class="subtitle">Join our library and get started today!</p>
    <form id="signupForm" method="POST" onsubmit="return validateForm();" >

      <div class="input-group">
        <label for="userId"><b>User ID</b><span class="redStar">*</span></label>
        <input type="text" id="userId" name="userId" required onfocus="initiate()"
        value="<?php if (isset($_POST['userId'])) echo htmlspecialchars($_POST['userId']); ?>" >
        <span id="userIdError" class="error"><?=$message?></span>
      </div>

      <div class="input-group">
          <label for="password"><b>Password</b><span class="redStar">*</span></label>
          <div class="password-input-wrapper">
            <input type="password" id="password" name="password" required
            value="<?php if (isset($_POST['password'])) echo htmlspecialchars($_POST['password']); ?>">
            <span class="toggle-password" onclick="myFunction()"><i class="fa-solid fa-eye"></i></span>
          </div>
          <span id="passwordError" class="error"></span>
      </div>
        
      <div class="form-row">

        <div class="input-group">
          <label for="userType"><b>User Type</b><span class="redStar">*</span></label>
          <select id="userType" name="userType" required>
            <option value="">Please select your user type</option>
            <option value="Student" <?php if (isset($_POST['userType']) && $_POST['userType'] == 'Student') echo 'selected'; ?>>Student</option>
            <option value="Faculty" <?php if (isset($_POST['userType']) && $_POST['userType'] == 'Faculty') echo 'selected'; ?>>Faculty</option>
            <option value="Admin" <?php if (isset($_POST['userType']) && $_POST['userType'] == 'Admin') echo 'selected'; ?>>Admin</option>
          </select>
        </div>

        <div class="input-group">
          <label for="gender"><b>Gender</b></label>
          <select id="gender" name="gender">
          <option value="Male" <?php if (isset($_POST['gender']) && $_POST['gender'] == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if (isset($_POST['gender']) && $_POST['gender'] == 'Female') echo 'selected'; ?>>Female</option>
            <option value="Others" <?php if (isset($_POST['gender']) && $_POST['gender'] == 'Others') echo 'selected'; ?>>Others</option>
          </select>
        </div>

      </div>
     
      <div class="input-group">
        <label for="dob"><b>Date of birth</b></label>
        <input type="date" id="dob" name="dob" 
        value="<?php if (isset($_POST['dob'])) echo htmlspecialchars($_POST['dob']); ?>">
      </div>

      <div class="input-group">
        <label for="emailId"><b>Email ID</b><span class="redStar">*</span></label>
        <input type="email" id="emailId" name="emailId" required
        value="<?php if (isset($_POST['emailId'])) echo htmlspecialchars($_POST['emailId']); ?>">
        <span id="emailIdError" class="error"></span>
      </div>

      <div class="input-group">
        <label for="phoneNumber"><b>Phone Number</b><span class="redStar">*</span></label>
        <input type="tel" id="phoneNumber" name="phoneNumber" required
        value="<?php if (isset($_POST['phoneNumber'])) echo htmlspecialchars($_POST['phoneNumber']); ?>">
        <span id="phoneNumberError" class="error"></span>
      </div>
      
      <div class="input-group">
        <label for="address"><b>Address</b></label>
        <textarea id="address" name="address" maxlength="255"
        ><?php if (isset($_POST['address'])) echo htmlspecialchars($_POST['address']); ?></textarea>
        <span id="addressError" class="error"></span>
      </div>

      <button type="submit">Register</button>

      <div class="login-link">
          <p>Already have an account? <a href="#" id="loginLink">Login Here</a></p>
      </div>
    </form>
  </div>
</body>
</html>