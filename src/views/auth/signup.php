<?php
$pageTitle = 'Sign Up';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    
    .auth-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        animation: slideInUp 0.5s ease-out;
        max-width: 800px;
        width: 100%;
        margin: 0 auto;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .auth-header {
        text-align: center;
        padding: 2rem 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        border-bottom: 1px solid rgba(102, 126, 234, 0.12);
    }
    
    .auth-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        font-size: 1.75rem;
        color: white;
        box-shadow: 0 8px 16px rgba(102, 126, 234, 0.25);
    }
    
    .auth-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .auth-subtitle {
        color: #6b7280;
        font-size: 1rem;
    }
    
    .auth-body {
        padding: 2rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        font-size: 0.9375rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .required-star {
        color: #dc2626;
    }
    
    .input-group-modern {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
        transition: color 0.2s ease;
    }
    
    .form-control-modern,
    .form-select-modern,
    .form-textarea-modern {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
        background: #f9fafb;
    }
    
    .form-select-modern {
        padding-right: 2.5rem;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
    }
    
    .form-textarea-modern {
        min-height: 80px;
        resize: vertical;
        padding-top: 0.75rem;
    }
    
    .form-control-modern:focus,
    .form-select-modern:focus,
    .form-textarea-modern:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
    }
    
    .form-control-modern:focus + .input-icon,
    .form-select-modern:focus + .input-icon,
    .form-textarea-modern:focus + .input-icon {
        color: #667eea;
    }
    
    .checkbox-modern {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.875rem;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    
    .checkbox-modern:hover {
        background: rgba(102, 126, 234, 0.08);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .checkbox-modern input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #667eea;
        margin-top: 2px;
    }
    
    .checkbox-label {
        font-size: 0.9375rem;
        color: #4b5563;
        line-height: 1.5;
    }
    
    .checkbox-label a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    
    .checkbox-label a:hover {
        text-decoration: underline;
    }
    
    .btn-auth {
        width: 100%;
        padding: 0.875rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
        margin-top: 1.5rem;
    }
    
    .btn-auth:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.35);
    }
    
    .btn-auth:active {
        transform: translateY(0);
    }
    
    .btn-auth i {
        margin-right: 0.5rem;
    }
    
    .auth-footer {
        text-align: center;
        padding: 1.5rem;
        border-top: 1px solid rgba(102, 126, 234, 0.12);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
    }
    
    .auth-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    
    .auth-link:hover {
        color: #764ba2;
        text-decoration: underline;
    }
    
    @media (max-width: 640px) {
        .auth-container {
            padding: 20px 16px;
        }
        
        .auth-card {
            border-radius: 12px;
        }
        
        .auth-header {
            padding: 1.5rem 1rem;
        }
        
        .auth-body {
            padding: 1.5rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 0.875rem;
        }
        
        .auth-title {
            font-size: 1.5rem;
        }
        
        .auth-subtitle {
            font-size: 0.9375rem;
        }
        
        .btn-auth {
            padding: 0.75rem;
        }
    }
</style>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="auth-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="auth-title">Create Your Account</h2>
                        <p class="auth-subtitle">Join the University Library community - Choose your username and Student ID will be auto-generated</p>
                    </div>
                    
                    <div class="auth-body">
                        <form method="POST" action="<?= BASE_URL ?>signup">
                            <!-- Username -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    Username <span class="required-star">*</span>
                                </label>
                                <div class="input-group-modern">
                                    <input type="text" 
                                           class="form-control-modern" 
                                           id="username" 
                                           name="username"
                                           placeholder="Choose your username" 
                                           required
                                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- Password -->
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    Password <span class="required-star">*</span>
                                </label>
                                <div class="input-group-modern">
                                    <input type="password" 
                                           class="form-control-modern" 
                                           id="password" 
                                           name="password"
                                           placeholder="Create a strong password" 
                                           required>
                                    <i class="fas fa-lock input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- Gender -->
                            <div class="form-group">
                                <label for="gender" class="form-label">
                                    Gender <span class="required-star">*</span>
                                </label>
                                <div class="input-group-modern">
                                    <select class="form-select-modern" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= ($_POST['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <i class="fas fa-venus-mars input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- Email & Phone -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="emailId" class="form-label">
                                        Email Address <span class="required-star">*</span>
                                    </label>
                                    <div class="input-group-modern">
                                        <input type="email" 
                                               class="form-control-modern" 
                                               id="emailId" 
                                               name="emailId"
                                               placeholder="your.email@example.com" 
                                               required
                                               value="<?= htmlspecialchars($_POST['emailId'] ?? '') ?>">
                                        <i class="fas fa-envelope input-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phoneNumber" class="form-label">
                                        Phone Number <span class="required-star">*</span>
                                    </label>
                                    <div class="input-group-modern">
                                        <input type="tel" 
                                               class="form-control-modern" 
                                               id="phoneNumber" 
                                               name="phoneNumber"
                                               placeholder="+94 XXX XXX XXX" 
                                               required
                                               value="<?= htmlspecialchars($_POST['phoneNumber'] ?? '') ?>">
                                        <i class="fas fa-phone input-icon"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Date of Birth -->
                            <div class="form-group">
                                <label for="dob" class="form-label">
                                    Date of Birth <span class="required-star">*</span>
                                </label>
                                <div class="input-group-modern">
                                    <input type="date" 
                                           class="form-control-modern" 
                                           id="dob" 
                                           name="dob" 
                                           required
                                           value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
                                    <i class="fas fa-calendar-alt input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="form-group">
                                <label for="address" class="form-label">
                                    Address <span class="required-star">*</span>
                                </label>
                                <div class="input-group-modern">
                                    <textarea class="form-textarea-modern" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              placeholder="Enter your complete address" 
                                              required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                </div>
                            </div>
                            
                            <!-- Terms & Conditions -->
                            <div class="form-group">
                                <div class="checkbox-modern">
                                    <input type="checkbox" id="terms" required>
                                    <label class="checkbox-label" for="terms">
                                        I agree to the <a href="#">Terms and Conditions</a> and 
                                        <a href="#">Privacy Policy</a> of the University Library
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="btn-auth">
                                <i class="fas fa-user-plus"></i>
                                Create Account
                            </button>
                        </form>
                    </div>
                    
                    <div class="auth-footer">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="<?= BASE_URL ?>" class="auth-link">
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