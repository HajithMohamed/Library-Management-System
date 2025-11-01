<?php

include APP_ROOT . '/views/layouts/header.php';

/**
 * @var array $user
 * @var array $errors
 * @var string $success
 */

?>

<style>
.profile-container {
    padding: 2rem 0;
    animation: fadeIn 0.6s ease-out;
}

.profile-header {
    text-align: center;
    margin-bottom: 2rem;
}

.profile-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.profile-header p {
    font-size: 1.2rem;
    color: #666;
}

.alert {
    margin-bottom: 1.5rem;
}

.profile-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.profile-card-header {
    background: #f7f7f7;
    border-bottom: 1px solid #eaeaea;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
}

.profile-card-header i {
    font-size: 2rem;
    color: #007bff;
    margin-right: 1rem;
}

.profile-card-header h3 {
    font-size: 1.75rem;
    margin: 0;
}

.profile-card-body {
    padding: 1.5rem;
}

.profile-image-section {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.profile-avatar {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 1.5rem;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #007bff;
    color: #fff;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.profile-image-info {
    flex: 1;
}

.profile-image-info h4 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.profile-image-info p {
    font-size: 0.9rem;
    color: #666;
}

.info-box {
    background: #f9f9f9;
    border-left: 4px solid #007bff;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 0.5rem;
}

.info-box-content {
    display: flex;
    align-items: center;
}

.info-box-icon {
    font-size: 2rem;
    color: #007bff;
    margin-right: 1rem;
}

.info-box-text h5 {
    font-size: 1.2rem;
    margin: 0 0 0.5rem 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.form-group {
    position: relative;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
}

.required-star {
    color: red;
    margin-left: 0.2rem;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    border-color: #007bff;
    outline: none;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
}

.btn-cancel {
    background: #f44336;
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    text-decoration: none;
    margin-right: 1rem;
    display: inline-block;
}

.btn-cancel:hover {
    background: #d32f2f;
}

.btn-submit {
    background: #007bff;
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    border: none;
    cursor: pointer;
    display: inline-block;
    font-size: 1rem;
    transition: background 0.3s;
}

.btn-submit:hover {
    background: #0056b3;
}

.disabled-badge {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #e0e0e0;
    color: #666;
    padding: 0.5rem 1rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.disabled-badge i {
    margin-right: 0.5rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>

<div class="profile-container">
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <h1>Your Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>

        <?php if (isset($_SESSION['success'])) : ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])) : ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Profile Information Card -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="fas fa-user-circle"></i>
                        <h3>Profile Information</h3>
                    </div>
                    <div class="profile-card-body">
                        <?php
                        $profileUrl = BASE_URL . 'assets/images/profileImg.jfif';
                        if (!empty($user['profileImage']) && file_exists(APP_ROOT . '/public/' . ltrim($user['profileImage'], '/'))) {
                            $profileUrl = BASE_URL . ltrim($user['profileImage'], '/');
                        }
                        ?>
                        <form method="POST" action="/faculty/profile" enctype="multipart/form-data">
                            <input type="hidden" name="update_profile" value="1">
                            <!-- Profile Image Section -->
                            <div class="profile-image-section">
                                <div class="profile-avatar">
                                    <img src="<?= htmlspecialchars($profileUrl) ?>" alt="Profile Image" id="profilePreview">
                                    <label for="profileImage" class="profile-avatar-badge" style="cursor: pointer;">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input id="profileImage" name="profileImage" type="file" class="d-none" accept="image/jpeg,image/png">
                                </div>
                                <div class="profile-image-info">
                                    <h4>Profile Picture</h4>
                                    <p>Upload a JPG or PNG image. Maximum file size is 2 MB. Recommended size: 400x400px.</p>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-content">
                                    <div class="info-box-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="info-box-text">
                                        <h5>Account Security</h5>
                                        <p>Some fields like Faculty ID and Username cannot be changed for security reasons. Contact library administration if you need to update these details.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-id-card"></i> Faculty ID</label>
                                    <input type="text" class="form-input" value="<?= htmlspecialchars($user['userId'] ?? '') ?>" disabled>
                                    <span class="disabled-badge"><i class="fas fa-lock"></i> Cannot be changed</span>
                                </div>
                                <div class="form-group">
                                    <label for="name" class="form-label"><i class="fas fa-user"></i> Name <span class="required-star">*</span></label>
                                    <input type="text" id="name" name="name" class="form-input" required value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email Address <span class="required-star">*</span></label>
                                    <input id="email" name="email" type="email" class="form-input" placeholder="your.email@example.com" required value="<?= htmlspecialchars($user['emailId'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="phoneNumber" class="form-label"><i class="fas fa-phone"></i> Phone Number <span class="required-star">*</span></label>
                                    <input id="phoneNumber" name="phoneNumber" type="tel" class="form-input" placeholder="+94 XXX XXX XXX" required value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="gender" class="form-label"><i class="fas fa-venus-mars"></i> Gender <span class="required-star">*</span></label>
                                    <select id="gender" name="gender" class="form-select" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?= (($user['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= (($user['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= (($user['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="dob" class="form-label"><i class="fas fa-calendar-alt"></i> Date of Birth <span class="required-star">*</span></label>
                                    <input id="dob" name="dob" type="date" class="form-input" required value="<?= htmlspecialchars(date('Y-m-d', strtotime($user['dob'] ?? ''))) ?>">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="address" class="form-label"><i class="fas fa-map-marker-alt"></i> Address <span class="required-star">*</span></label>
                                    <textarea id="address" name="address" class="form-textarea" placeholder="Enter your complete address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="form-actions">
                                <a href="/faculty/dashboard" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                                <button type="submit" name="update_profile" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Change Password Card -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="fas fa-key"></i>
                        <h3>Change Password</h3>
                    </div>
                    <div class="profile-card-body">
                        <form action="/faculty/profile" method="post">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="current_password" class="form-label"><i class="fas fa-lock"></i> Current Password</label>
                                    <input type="password" name="current_password" id="current_password" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password" class="form-label"><i class="fas fa-key"></i> New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label"><i class="fas fa-key"></i> Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="change_password" class="btn-submit"><i class="fas fa-sync-alt"></i> Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profileImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>