<?php

require_once __DIR__ . '/../partials/header.php';

/**
 * @var array $user
 * @var array $errors
 * @var string $success
 */

?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Profile Management</h1>
            <hr>

            <?php if (isset($success)) : ?>
                <div class="alert alert-success">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && !empty($errors)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <form action="/faculty/profile" method="post">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= $user['username'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= $user['emailId'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="Male" <?= (isset($user['gender']) && $user['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (isset($user['gender']) && $user['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" class="form-control" value="<?= isset($user['dob']) ? date('Y-m-d', strtotime($user['dob'])) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="text" name="phoneNumber" id="phoneNumber" class="form-control" value="<?= $user['phoneNumber'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" class="form-control"><?= $user['address'] ?? '' ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="/faculty/profile" method="post">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>