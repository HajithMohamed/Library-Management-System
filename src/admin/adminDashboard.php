<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
$userId = $_SESSION['userId'];//Fetching userId and userType from session data
$userType = $_SESSION['userType'];
if ($userType != 'Admin') // If an invalid userType tries to access this page
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

//Fetching complete user details from database using the userId obtained from session
include DIR_URL.'config/dbConnection.php';
$sql="SELECT * FROM users WHERE userId='$userId'";
$retval=mysqli_query($conn,$sql);
$row=mysqli_fetch_assoc($retval);
$password=$row['password'];
$gender=$row['gender'];
$dob=$row['dob'];
$phoneNumber=$row['phoneNumber'];
$emailId=$row['emailId'];
$address=$row['address'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
<link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/dashboard.css" />
</head>

<body>
<div class="main-container">
  <!-- Slideshow container -->
  <div class="slideshow-container">
    <div class="welcome-text">Welcome to the Library!</div>

    <div class="mySlides fade">
      <img src="<?php echo BASE_URL;?>assets/images/carousalImg1.jfif" alt="Library Image 1">
    </div>

    <div class="mySlides fade">
      <img src="<?php echo BASE_URL;?>assets/images/carousalImg2.jfif" alt="Library Image 2">
    </div>

    <div class="mySlides fade">
      <img src="<?php echo BASE_URL;?>assets/images/carousalImg3.jfif" alt="Library Image 3">
    </div>
  </div>

  <!-- Dots below carousel -->
  <div class="dots-container">
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="dot"></span>
  </div>

  <!-- Main content area -->
  <div class="content-area">
    <!-- Buttons below the carousel -->
    <div class="buttons">
      <button type="button" class="button search" id="Manage inventory">MANAGE INVENTORY</button>
      <button type="button" class="button issue" id="Add books">ADD BOOKS</button>
      <button type="button" class="button return" id="Remove books">REMOVE BOOKS</button>
      <button type="button" class="button pay" id="View members">VIEW MEMBERS</button>
    </div>
  </div>

  <!-- Sidebar overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="<?php echo BASE_URL;?>assets/images/profileImg.jfif" alt="Profile Picture" class="profile-img">
      <div class="user-id"><?php echo htmlspecialchars($_SESSION['userId']); ?></div>
    </div>

    <nav class="sidebar-nav">
      <a href="#" id="viewProfile">
        <i class="fa-solid fa-user"></i>
        <span>View Profile</span>
      </a>

      <a href="#" id="logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </nav>
  </div>

  <!-- Toggle Button -->
  <div class="toggle-button" id="toggleBtn" aria-label="Toggle Sidebar"></div>

  <!-- Modal content -->
  <div id="userModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>User Details</h2>
        <span class="close" aria-label="Close Modal">&times;</span>
      </div>
      
      <form>
        <div class="form-group">
          <label for="userType">User Type:</label>
          <input type="text" id="userType" value="<?php echo htmlspecialchars($userType); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="userId">User ID:</label>
          <input type="text" id="userId" value="<?php echo htmlspecialchars($userId); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="gender">Gender:</label>
          <input type="text" id="gender" value="<?php echo htmlspecialchars($gender); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="dob">Date of Birth:</label>
          <input type="text" id="dob" value="<?php echo (empty($dob) ? "" : date("d/m/Y", strtotime($dob))); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="text" id="email" value="<?php echo htmlspecialchars($emailId); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="phone">Phone Number:</label>
          <input type="text" id="phone" value="<?php echo htmlspecialchars($phoneNumber); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="address">Address:</label>
          <textarea id="address" disabled><?php echo htmlspecialchars($address); ?></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Carousel functionality
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  // Hide all slides
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }

  // Increment slide index
  slideIndex++;
  if (slideIndex > slides.length) {
    slideIndex = 1;
  }

  // Deactivate all dots
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  // Display the current slide and activate corresponding dot
  slides[slideIndex - 1].style.display = "block";
  dots[slideIndex - 1].className += " active";

  // Automatically change slides every 2 seconds
  setTimeout(showSlides, 2000);
}

// Sidebar Toggle Script
const toggleBtn = document.getElementById('toggleBtn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function toggleSidebar() {
  sidebar.classList.toggle('open');
  toggleBtn.classList.toggle('active');
  sidebarOverlay.classList.toggle('active');
}

toggleBtn.addEventListener('click', toggleSidebar);
sidebarOverlay.addEventListener('click', toggleSidebar);

// Close sidebar on escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape' && sidebar.classList.contains('open')) {
    toggleSidebar();
  }
});

// Modal functionality
const modal = document.getElementById('userModal');
const btn = document.getElementById('viewProfile');
const span = document.getElementsByClassName('close')[0];

// Open modal on button click
btn.onclick = function() {
  modal.style.display = 'block';
}

// Close modal on clicking the 'x'
span.onclick = function() {
  modal.style.display = 'none';
}

// Close modal on clicking outside the modal
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape' && modal.style.display === 'block') {
    modal.style.display = 'none';
  }
});

//Logout button
document.getElementById('logout').addEventListener('click', function(e) {
  e.preventDefault();
  fetch('<?php echo BASE_URL;?>/src/global/logout.php', { method: 'POST' })
    .then(() => {
      window.location.href='<?php echo BASE_URL;?>'; // Close window after destroying session data
    });
});

//View Members button
document.getElementById('View members').onclick=function(){
  window.location.href='ViewMembers.php';
}
//Add Books to the library button
document.getElementById('Add books').onclick=function(){
  window.location.href='AddBooks.php';
}
//Remove Books from the library button
document.getElementById('Remove books').onclick=function(){
  window.location.href='RemoveBooks.php';
}
//Manage Inventory button
document.getElementById('Manage inventory').onclick=function(){
  window.location.href='ManageInventory.php';
}
</script>

</body>
<?php
mysqli_close($conn);
?>
</html>