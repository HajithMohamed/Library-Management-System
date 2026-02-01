<?php include APP_ROOT . '/views/templates/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Two-Factor Authentication</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-center">Please enter the code from your authenticator app.</p>

                    <form method="POST" action="<?php echo BASE_URL; ?>/2fa/verify">
                        <div class="form-group mb-3 is-invalid">
                            <label for="code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="code" name="code"
                                placeholder="Enter 6-digit code" required autofocus autocomplete="off">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="trust_device" name="trust_device"
                                value="1">
                            <label class="form-check-label" for="trust_device">
                                Trust this device for 30 days
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </div>
                    </form>

                    <hr>
                    <div class="text-center">
                        <button type="button" class="btn btn-link btn-sm" data-bs-toggle="collapse"
                            data-bs-target="#backupCodeParams">
                            Use Backup Code
                        </button>
                    </div>

                    <div class="collapse mt-3" id="backupCodeParams">
                        <form method="POST" action="<?php echo BASE_URL; ?>/2fa/verify">
                            <div class="form-group mb-2">
                                <label for="backup_code">Backup Code</label>
                                <input type="text" class="form-control" id="backup_code" name="backup_code"
                                    placeholder="Enter backup code">
                            </div>
                            <button type="submit" class="btn btn-secondary btn-sm w-100">Verify with Backup
                                Code</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/templates/footer.php'; ?>