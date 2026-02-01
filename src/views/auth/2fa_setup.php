<?php include APP_ROOT . '/views/templates/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Setup Two-Factor Authentication</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <p>Scan the following QR code with your Google Authenticator app (or compatible app).</p>

                    <div class="text-center mb-4">
                        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid border p-2">
                    </div>

                    <p class="text-muted text-center">Secret Key: <code><?php echo $secret; ?></code></p>

                    <form method="POST" action="<?php echo BASE_URL; ?>/2fa/verify-setup">
                        <input type="hidden" name="secret" value="<?php echo $secret; ?>">

                        <div class="form-group mb-3">
                            <label for="code">Enter Verification Code</label>
                            <input type="text" class="form-control" id="code" name="code"
                                placeholder="Enter 6-digit code" required autocomplete="off">
                            <small class="text-muted">Enter the code shown in your app to confirm setup.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Verify and Enable</button>
                            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/templates/footer.php'; ?>