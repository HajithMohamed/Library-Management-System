<?php 
  include 'config/config.php';
  include DIR_URL.'config/dbConnection.php'; // connect to database
  session_start();
  include DIR_URL.'src/global/redirect.php'; // prevent access to this page by redirecting to respective dashboard if a session is active

  $userIdError="";
  $passwordError="";

  if($_SERVER["REQUEST_METHOD"]==="POST"){
    include DIR_URL.'src/global/login.php'; // authentication of user credentials
    mysqli_close($conn);
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Integrated Library System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="An Integrated Library System (ILS) for managing book inventory, user authentication, student and faculty access, and admin-level controls. Efficient and responsive system for academic institutions.">
  <meta name="author" content="Debrup Chatterjee">
  <script src="./assets/js/jquery-3.7.1.js"></script> <!-- Jquery source file -->
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/login.css" />
  <script>
    // "LMS Login Portal" Message Typewriter Animation(executed only on first load of the page)
    document.addEventListener("DOMContentLoaded", () => {
      if (!sessionStorage.getItem("firstLoad")) 
      {
        $(document).ready(function () {
          var text = "User Login";
          var index = 0;
          function writeText() {
            $("#typewriter").text(text.substring(0, index + 1));
            index++;
            if (index <= text.length) {
              setTimeout(writeText, 150);
            }
          }
          writeText();
        });
        sessionStorage.setItem("firstLoad", "true");
      }
    });

    //Show Password Functionality
    function myFunction() {
      var x = document.getElementById("password");
      x.type = (x.type === "password") ? "text" : "password";
    }
    
    // Unchecks the Show Password checkbox
    function uncheckCheckbox() {
      const checkbox = document.getElementById("checkbox");
      checkbox.checked = false;
    }

    //Signup Link 
    document.addEventListener("DOMContentLoaded", function () {
      document.getElementById("signupBtn").onclick = function () {
        window.open("<?php echo BASE_URL;?>src/global/signup.php", "_blank");
      };
    });
  </script>
</head>

<body onload="uncheckCheckbox()">

  <video autoplay muted loop playsinline class="video-bg" poster="<?php echo BASE_URL;?>assets/images/background.png">
    <source src="<?php echo BASE_URL;?>assets/videos/background.mp4" type="video/mp4"> 
    <img src="<?php echo BASE_URL;?>assets/images/background.png" alt="Background Image" /><!-- shown as background if the video fails to load -->
  </video>

  <div class="container">
     <div id="typewriter">User Login</div><br>

    <form method="POST">

      <input type="text" id="userId" name="userId" placeholder="Enter your UserID" required class="<?php if(!empty($userIdError)) echo 'input-error'; ?>"
        onfocus="document.getElementById('userIdError').innerHTML=''" value="<?php if (isset($_POST['userId'])) echo htmlspecialchars($_POST['userId']); ?>">
      <span id="userIdError" class="error"><?= $userIdError ?></span>

      <input type="password" id="password" name="password" placeholder="Enter your password" required class="<?php if(!empty($passwordError)) echo 'input-error'; ?>"
        onfocus="document.getElementById('passwordError').innerHTML=''" value="<?php if (isset($_POST['password'])) echo $_POST['password']; ?>">
      <span id="passwordError" class="error"><?= $passwordError ?></span>

      <div class="checkbox">
        &nbsp;&nbsp; <input type="checkbox" id="checkbox" onclick="myFunction()"> <label for="checkbox">Show Password</label>
      </div>

      <br>
      <button type="submit" class="button">Login</button>
      <br><br>

      <center>
        <div class="signup">
          Don't have an account?&nbsp;&nbsp;<a href="#" id="signupBtn">Sign Up</a>
        </div>
      </center>

    </form>
  </div>

</body>
</html>