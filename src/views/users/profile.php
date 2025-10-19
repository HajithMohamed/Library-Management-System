<?php
$pageTitle = 'Your Profile';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .profile-container { padding: 24px 0; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,.08); overflow: hidden; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, rgba(102,126,234,.05), rgba(118,75,162,.05)); }
    .card-title { margin: 0; font-size: 1.25rem; font-weight: 700; color: #1f2937; }
    .card-body { padding: 20px; }
    .form-group { margin-bottom: 12px; }
    label { display:block; font-weight: 600; color: #374151; margin-bottom: 6px; }
    input, select, textarea { width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb; }
    textarea { min-height: 80px; }
    .btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; border:0; padding:10px 14px; border-radius:8px; cursor:pointer; font-weight:700; }
</style>

<div class="container profile-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profile</h3>
                </div>
                <div class="card-body">
                    <?php
                        $userIdSafe = htmlspecialchars($user['userId'] ?? '');
                        $uploadsDir = APP_ROOT . '/public/assets/images/users/';
                        $possible = [
                            $uploadsDir . ($user['userId'] ?? '') . '.jpg',
                            $uploadsDir . ($user['userId'] ?? '') . '.jpeg',
                            $uploadsDir . ($user['userId'] ?? '') . '.png'
                        ];
                        $profilePath = '';
                        // Prefer DB path if set and file exists
                        if (!empty($user['profileImage'])) {
                            $candidate = APP_ROOT . '/public/' . ltrim($user['profileImage'], '/');
                            if (file_exists($candidate)) { $profilePath = $candidate; }
                        }
                        if (empty($profilePath)) {
                            foreach ($possible as $p) { if ($user && !empty($user['userId']) && file_exists($p)) { $profilePath = $p; break; } }
                        }
                        $profileUrl = !empty($profilePath)
                            ? BASE_URL . 'assets/images/users/' . basename($profilePath)
                            : BASE_URL . 'assets/images/profileImg.jfif';
                    ?>

                    <div style="display:flex; gap:16px; align-items:center; margin-bottom:16px;">
                        <img src="<?= htmlspecialchars($profileUrl) ?>" alt="Profile Image" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid rgba(102,126,234,.35);">
                        <div style="color:#6b7280">Upload a JPG or PNG under 2 MB.</div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>user/profile" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" value="<?= htmlspecialchars($user['userId'] ?? '') ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="profileImage">Profile Image</label>
                            <input id="profileImage" name="profileImage" type="file" accept="image/jpeg,image/png">
                        </div>
                        <div class="form-group">
                            <label for="emailId">Email</label>
                            <input id="emailId" name="emailId" type="email" required value="<?= htmlspecialchars($user['emailId'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input id="phoneNumber" name="phoneNumber" type="tel" required value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male" <?= (($user['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (($user['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= (($user['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input id="dob" name="dob" type="date" required value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <button class="btn" type="submit">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>


