<?php
$pageTitle = 'Sign Up';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="card-title">
                            <i class="fas fa-user-plus text-primary"></i> Create Account
                        </h2>
                        <p class="text-muted">Join the University Library</p>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>signup">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="userId" class="form-label">User ID *</label>
                                <input type="text" class="form-control" id="userId" name="userId" 
                                       placeholder="Enter User ID" required
                                       value="<?= htmlspecialchars($_POST['userId'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="userType" class="form-label">User Type *</label>
                                <select class="form-select" id="userType" name="userType" required>
                                    <option value="">Select User Type</option>
                                    <option value="Student" <?= ($_POST['userType'] ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                                    <option value="Faculty" <?= ($_POST['userType'] ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender *</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($_POST['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emailId" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="emailId" name="emailId" 
                                       placeholder="Enter email address" required
                                       value="<?= htmlspecialchars($_POST['emailId'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" 
                                       placeholder="Enter phone number" required
                                       value="<?= htmlspecialchars($_POST['phoneNumber'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="dob" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="dob" name="dob" required
                                   value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                      placeholder="Enter your address" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? 
                            <a href="<?= BASE_URL ?>" class="text-decoration-none">
                                Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
