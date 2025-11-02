<?php
$pageTitle = 'Admin Profile';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    overflow-x: hidden;
    background: #f8fafc;
  }

  /* Main Layout Container */
  .admin-layout {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
  }

  /* Main Content Area */
  .main-content {
    flex: 1;
    margin-left: 280px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-height: 100vh;
  }

  .sidebar.collapsed~.main-content {
    margin-left: 80px;
  }

  /* Top Header */
  .top-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 1.5rem 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(102, 126, 234, 0.1);
  }

  .header-left h1 {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.25rem;
    letter-spacing: -0.5px;
  }

  .breadcrumb {
    display: flex;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.9rem;
    align-items: center;
  }

  .breadcrumb span:not(:last-child)::after {
    content: '';
    width: 4px;
    height: 4px;
    background: #cbd5e1;
    border-radius: 50%;
    display: inline-block;
    margin-left: 0.5rem;
  }

  .header-right {
    display: flex;
    gap: 0.75rem;
    align-items: center;
  }

  .header-btn {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 0.625rem 1.25rem;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    position: relative;
    overflow: hidden;
  }

  .header-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s ease;
  }

  .header-btn:hover::before {
    left: 100%;
  }

  .header-btn:hover {
    background: #f8fafc;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
  }

  /* Dashboard Content */
  .dashboard-content {
    padding: 2rem;
    animation: fadeIn 0.5s ease-in-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Profile Content Card */
  .content-card {
    background: white;
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
    border: 1px solid rgba(102, 126, 234, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .content-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  }

  .content-card:hover {
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.12);
    transform: translateY(-2px);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
    border-bottom: 2px solid #f1f5f9;
    position: relative;
  }

  .card-header::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  }

  .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    letter-spacing: -0.5px;
  }

  .card-title i {
    font-size: 1.75rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: pulse 2s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
  }

  /* Profile Image Section */
  .profile-image-section {
    display: flex;
    align-items: center;
    gap: 2.5rem;
    padding: 2.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
    border-radius: 20px;
    margin-bottom: 2.5rem;
    border: 2px dashed rgba(102, 126, 234, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .profile-image-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
  }

  @keyframes rotate {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }

  .profile-image-section:hover {
    border-color: rgba(102, 126, 234, 0.6);
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
  }

  .profile-avatar {
    position: relative;
    z-index: 1;
  }

  .profile-avatar img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: 0 15px 45px rgba(102, 126, 234, 0.4);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  .profile-avatar:hover img {
    transform: scale(1.08) rotate(3deg);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.5);
    border-color: rgba(118, 75, 162, 0.8);
  }

  .profile-avatar-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    border: 4px solid white;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    animation: bounce 2s ease-in-out infinite;
  }

  @keyframes bounce {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.1);
    }
  }

  .profile-image-info {
    flex: 1;
    z-index: 1;
  }

  .profile-image-info h4 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
    letter-spacing: -0.3px;
  }

  .profile-image-info p {
    color: #64748b;
    margin: 0 0 1rem 0;
    font-size: 0.95rem;
    line-height: 1.6;
  }

  .admin-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.875rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255, 255, 255, 0.2);
  }

  .admin-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  }

  .admin-badge i {
    font-size: 1rem;
    animation: sparkle 1.5s ease-in-out infinite;
  }

  @keyframes sparkle {
    0%, 100% {
      opacity: 1;
      transform: scale(1);
    }
    50% {
      opacity: 0.7;
      transform: scale(1.2);
    }
  }

  /* Info Box */
  .info-box {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(147, 51, 234, 0.08));
    backdrop-filter: blur(10px);
    border: 2px solid rgba(59, 130, 246, 0.2);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .info-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.6s ease;
  }

  .info-box:hover::before {
    left: 100%;
  }

  .info-box:hover {
    border-color: rgba(59, 130, 246, 0.4);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(147, 51, 234, 0.12));
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.15);
  }

  .info-box-content {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    position: relative;
    z-index: 1;
  }

  .info-box-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
    color: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .info-box:hover .info-box-icon {
    transform: rotate(10deg) scale(1.1);
    box-shadow: 0 12px 30px rgba(59, 130, 246, 0.4);
  }

  .info-box-text h5 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.3px;
  }

  .info-box-text p {
    color: #64748b;
    margin: 0;
    line-height: 1.7;
    font-size: 0.95rem;
  }

  /* Form Layout */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.75rem;
    margin-bottom: 1.75rem;
  }

  .form-group {
    margin-bottom: 0;
    animation: slideUp 0.5s ease-out backwards;
  }

  .form-group:nth-child(1) { animation-delay: 0.05s; }
  .form-group:nth-child(2) { animation-delay: 0.1s; }
  .form-group:nth-child(3) { animation-delay: 0.15s; }
  .form-group:nth-child(4) { animation-delay: 0.2s; }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .form-group.full-width {
    grid-column: 1 / -1;
  }

  .form-label {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    letter-spacing: -0.2px;
    transition: color 0.3s ease;
  }

  .form-group:focus-within .form-label {
    color: #667eea;
  }

  .form-label i {
    color: #667eea;
    font-size: 1.1rem;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .form-group:focus-within .form-label i {
    transform: scale(1.2) rotate(5deg);
  }

  .required-star {
    color: #ef4444;
    margin-left: 2px;
    font-weight: 700;
    animation: blink 2s ease-in-out infinite;
  }

  @keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }

  .form-input,
  .form-select,
  .form-textarea {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #f8fafc;
    font-family: inherit;
    font-weight: 500;
    position: relative;
  }

  .form-input:hover,
  .form-select:hover,
  .form-textarea:hover {
    border-color: #cbd5e1;
    background: #ffffff;
  }

  .form-input:focus,
  .form-select:focus,
  .form-textarea:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12), 0 4px 20px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
  }

  .form-input:disabled {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #94a3b8;
    cursor: not-allowed;
    border-style: dashed;
  }

  .form-textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.6;
  }

  .form-file {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    font-weight: 500;
    position: relative;
    overflow: hidden;
  }

  .form-file::before {
    content: '📁';
    position: absolute;
    left: 1rem;
    font-size: 1.5rem;
    opacity: 0.3;
    transition: all 0.3s ease;
  }

  .form-file:hover {
    border-color: #667eea;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
  }

  .form-file:hover::before {
    opacity: 0.6;
    transform: scale(1.2) rotate(10deg);
  }

  .disabled-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid #fcd34d;
    transition: all 0.3s ease;
  }

  .disabled-badge:hover {
    transform: translateX(4px);
  }

  .disabled-badge i {
    font-size: 0.85rem;
    animation: shake 2s ease-in-out infinite;
  }

  @keyframes shake {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-5deg); }
    75% { transform: rotate(5deg); }
  }

  /* Action Buttons */
  .form-actions {
    display: flex;
    gap: 1.25rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #f1f5f9;
    position: relative;
  }

  .form-actions::before {
    content: '';
    position: absolute;
    top: -2px;
    left: 0;
    width: 100px;
    height: 2px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 2rem;
    border-radius: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    font-size: 0.95rem;
    border: none;
    font-family: inherit;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.3px;
  }

  .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
  }

  .btn:active::before {
    width: 300px;
    height: 300px;
  }

  .btn i {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .btn-cancel {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #64748b;
    border: 2px solid #e2e8f0;
  }

  .btn-cancel:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    color: #475569;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
  }

  .btn-cancel:hover i {
    transform: rotate(-90deg);
  }

  .btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    border: 2px solid transparent;
    position: relative;
  }

  .btn-submit::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 14px;
  }

  .btn-submit:hover::after {
    opacity: 1;
  }

  .btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
  }

  .btn-submit:hover i {
    transform: scale(1.2) rotate(360deg);
  }

  .btn-submit span,
  .btn-submit i {
    position: relative;
    z-index: 1;
  }

  .btn-submit:active,
  .btn-cancel:active {
    transform: translateY(0);
  }

  /* Loading State */
  .btn.loading {
    pointer-events: none;
    opacity: 0.7;
  }

  .btn.loading i {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  /* Mobile Menu Button */
  .mobile-menu-btn {
    display: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-size: 1.5rem;
    color: white;
    cursor: pointer;
    padding: 0.625rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .mobile-menu-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
  }

  .mobile-menu-btn:active {
    transform: scale(0.95);
  }

  /* Success/Error Messages */
  .alert {
    padding: 1.25rem 1.5rem;
    border-radius: 14px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 500;
    animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 4px solid;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-color: #10b981;
  }

  .alert-error {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border-color: #ef4444;
  }

  /* Tooltip */
  [data-tooltip] {
    position: relative;
    cursor: help;
  }

  [data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-8px);
    padding: 0.5rem 1rem;
    background: #1e293b;
    color: white;
    border-radius: 8px;
    font-size: 0.85rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
  }

  [data-tooltip]:hover::after {
    opacity: 1;
    transform: translateX(-50%) translateY(-4px);
  }

  /* Progress Bar */
  .progress-bar {
    width: 100%;
    height: 4px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 0.5rem;
  }

  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: width 0.3s ease;
  }

  /* Mobile Responsive */
  @media (max-width: 768px) {
    .mobile-menu-btn {
      display: block;
    }

    .main-content {
      margin-left: 0;
    }

    .sidebar.collapsed~.main-content {
      margin-left: 0;
    }

    .dashboard-content {
      padding: 1.25rem;
    }

    .top-header {
      padding: 1.25rem;
    }

    .header-left h1 {
      font-size: 1.35rem;
    }

    .header-right {
      gap: 0.5rem;
    }

    .header-btn {
      padding: 0.5rem;
    }

    .header-btn span {
      display: none;
    }

    .content-card {
      padding: 1.5rem;
      border-radius: 20px;
    }

    .card-title {
      font-size: 1.25rem;
    }

    .profile-image-section {
      flex-direction: column;
      text-align: center;
      padding: 2rem 1.5rem;
    }

    .profile-avatar img {
      width: 120px;
      height: 120px;
    }

    .form-grid {
      grid-template-columns: 1fr;
    }

    .form-actions {
      flex-direction: column-reverse;
      gap: 1rem;
    }

    .btn {
      width: 100%;
      justify-content: center;
      padding: 1rem 1.5rem;
    }

    .info-box {
      padding: 1.25rem;
    }

    .info-box-icon {
      width: 42px;
      height: 42px;
      font-size: 1.1rem;
    }
  }

  /* Tablet Responsive */
  @media (max-width: 1024px) and (min-width: 769px) {
    .form-grid {
      grid-template-columns: 1fr;
    }

    .dashboard-content {
      padding: 1.5rem;
    }
  }

  /* Mobile Overlay */
  .mobile-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 999;
    animation: fadeIn 0.3s ease;
  }

  .mobile-overlay.show {
    display: block;
  }

  /* Scrollbar Styling */
  ::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f5f9;
  }

  ::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  }

  /* Print Styles */
  @media print {
    .sidebar,
    .top-header,
    .mobile-overlay,
    .form-actions {
      display: none !important;
    }

    .main-content {
      margin-left: 0 !important;
    }

    .content-card {
      box-shadow: none !important;
      border: 1px solid #e2e8f0 !important;
    }
  }
</style>

<!-- Mobile Overlay -->
<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Admin Layout -->
<div class="admin-layout">
  <!-- Left Sidebar -->
  <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Top Header -->
    <header class="top-header">
      <div class="header-left">
        <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
          <i class="fas fa-bars"></i>
        </button>
        <h1>Administrator Profile</h1>
        <div class="breadcrumb">
          <span>Home</span>
          <span>/</span>
          <span>Profile</span>
        </div>
      </div>
      <div class="header-right">
        <a href="<?= BASE_URL ?>admin/dashboard" class="header-btn">
          <i class="fas fa-arrow-left"></i>
          <span>Back to Dashboard</span>
        </a>
        <button class="header-btn notification-btn">
          <i class="fas fa-bell"></i>
        </button>
        <a href="<?= BASE_URL ?>logout" class="header-btn">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </div>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
      <!-- Profile Card -->
      <div class="content-card">
        <!-- Card Header -->
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-user-circle"></i>
            Personal Information
          </h2>
        </div>

        <!-- Card Body -->
        <?php
        // Get admin data from session or database
        $admin = $_SESSION['admin'] ?? [];

        // Profile image handling
        $profilePath = '';
        if (!empty($admin['userId'])) {
          $possible = [
            APP_ROOT . '/public/assets/images/admins/' . $admin['userId'] . '.jpg',
            APP_ROOT . '/public/assets/images/admins/' . $admin['userId'] . '.jpeg',
            APP_ROOT . '/public/assets/images/admins/' . $admin['userId'] . '.png'
          ];
          foreach ($possible as $p) {
            if (file_exists($p)) {
              $profilePath = $p;
              break;
            }
          }
        }
        $profileUrl = !empty($profilePath)
          ? BASE_URL . 'assets/images/admins/' . basename($profilePath)
          : BASE_URL . 'assets/images/profileImg.jfif';
        ?>

        <!-- Profile Image Section -->
        <div class="profile-image-section">
          <div class="profile-avatar">
            <img src="<?= htmlspecialchars($profileUrl) ?>" alt="Admin Profile Image" id="profilePreview">
            <div class="profile-avatar-badge">
              <i class="fas fa-shield-alt"></i>
            </div>
          </div>
          <div class="profile-image-info">
            <h4>Administrator Profile Picture</h4>
            <p>Upload a JPG or PNG image. Maximum file size is 2 MB. Recommended size: 400x400px.</p>
            <span class="admin-badge">
              <i class="fas fa-crown"></i>
              Administrator Account
            </span>
          </div>
        </div>

        <!-- Info Box -->
        <div class="info-box">
          <div class="info-box-content">
            <div class="info-box-icon">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-box-text">
              <h5>Account Security</h5>
              <p>Some fields like Admin ID and Username cannot be changed for security reasons. These fields are locked to maintain system integrity and accountability.</p>
            </div>
          </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>admin/profile" enctype="multipart/form-data">
          <!-- Basic Info Grid -->
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-id-badge"></i>
                Admin ID
              </label>
              <input type="text" class="form-input" value="<?= htmlspecialchars($admin['userId'] ?? '') ?>" disabled>
              <span class="disabled-badge">
                <i class="fas fa-lock"></i>
                Cannot be changed
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-user"></i>
                Username
              </label>
              <input type="text" class="form-input" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" disabled>
              <span class="disabled-badge">
                <i class="fas fa-lock"></i>
                Cannot be changed
              </span>
            </div>
          </div>

          <!-- Profile Image Upload -->
          <div class="form-grid">
            <div class="form-group full-width">
              <label for="profileImage" class="form-label">
                <i class="fas fa-image"></i>
                Change Profile Image
              </label>
              <input id="profileImage" name="profileImage" type="file" class="form-file" accept="image/jpeg,image/png">
            </div>
          </div>

          <!-- Contact Info -->
          <div class="form-grid">
            <div class="form-group">
              <label for="emailId" class="form-label">
                <i class="fas fa-envelope"></i>
                Email Address <span class="required-star">*</span>
              </label>
              <input id="emailId" name="emailId" type="email" class="form-input" placeholder="admin@library.com" required value="<?= htmlspecialchars($admin['emailId'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label for="phoneNumber" class="form-label">
                <i class="fas fa-phone"></i>
                Phone Number <span class="required-star">*</span>
              </label>
              <input id="phoneNumber" name="phoneNumber" type="tel" class="form-input" placeholder="+94 XXX XXX XXX" required value="<?= htmlspecialchars($admin['phoneNumber'] ?? '') ?>">
            </div>
          </div>

          <!-- Personal Info -->
          <div class="form-grid">
            <div class="form-group">
              <label for="gender" class="form-label">
                <i class="fas fa-venus-mars"></i>
                Gender <span class="required-star">*</span>
              </label>
              <select id="gender" name="gender" class="form-select" required>
                <option value="">Select Gender</option>
                <option value="Male" <?= (($admin['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (($admin['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= (($admin['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>

            <div class="form-group">
              <label for="dob" class="form-label">
                <i class="fas fa-calendar-alt"></i>
                Date of Birth <span class="required-star">*</span>
              </label>
              <input id="dob" name="dob" type="date" class="form-input" required value="<?= htmlspecialchars($admin['dob'] ?? '') ?>">
            </div>
          </div>

          <!-- Address -->
          <div class="form-grid">
            <div class="form-group full-width">
              <label for="address" class="form-label">
                <i class="fas fa-map-marker-alt"></i>
                Address <span class="required-star">*</span>
              </label>
              <textarea id="address" name="address" class="form-textarea" placeholder="Enter your complete address" required><?= htmlspecialchars($admin['address'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="form-actions">
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-cancel">
              <i class="fas fa-times"></i>
              Cancel
            </a>
            <button type="submit" class="btn btn-submit">
              <i class="fas fa-save"></i>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
  </main>
</div>

<script>
  // Toggle Sidebar with smooth animation
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    
    // Add ripple effect
    const btn = event.currentTarget;
    const ripple = document.createElement('span');
    ripple.style.cssText = `
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.6);
      width: 20px;
      height: 20px;
      animation: ripple 0.6s ease-out;
      pointer-events: none;
    `;
    btn.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  }

  // Toggle Mobile Sidebar with animation
  function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.mobile-overlay');
    
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
    
    // Prevent body scroll when sidebar is open
    document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
  }

  // Load sidebar state and add smooth entry animation
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
      sidebar.classList.add('collapsed');
    }

    // Add stagger animation to form groups
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
      group.style.animationDelay = `${index * 0.05}s`;
    });

    // Add focus animation to inputs
    const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
      });
      input.addEventListener('blur', function() {
        this.parentElement.classList.remove('focused');
      });
    });
  });

  // Enhanced image preview functionality
  document.getElementById('profileImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      // Validate file size (2MB max)
      if (file.size > 2 * 1024 * 1024) {
        showAlert('File size must be less than 2 MB', 'error');
        this.value = '';
        return;
      }

      // Validate file type
      if (!file.type.match('image/jpeg') && !file.type.match('image/png')) {
        showAlert('Only JPG and PNG images are allowed', 'error');
        this.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        const preview = document.getElementById('profilePreview');
        preview.style.opacity = '0';
        setTimeout(() => {
          preview.src = e.target.result;
          preview.style.opacity = '1';
          preview.style.transition = 'opacity 0.5s ease';
        }, 200);
      };
      reader.readAsDataURL(file);
      
      showAlert('Image selected successfully! Ready to upload.', 'success');
    }
  });

  // Enhanced form validation with visual feedback
  document.querySelector('form').addEventListener('submit', function(e) {
    const phoneNumber = document.getElementById('phoneNumber').value;
    const phoneRegex = /^[+]?[\d\s-()]+$/;

    if (!phoneRegex.test(phoneNumber)) {
      e.preventDefault();
      showAlert('Please enter a valid phone number', 'error');
      document.getElementById('phoneNumber').focus();
      return false;
    }

    // Add loading state to submit button
    const submitBtn = this.querySelector('.btn-submit');
    submitBtn.classList.add('loading');
    submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Saving Changes...';
    
    return true;
  });

  // Show alert messages
  function showAlert(message, type = 'success') {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
      existingAlert.remove();
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
      <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
      <span>${message}</span>
    `;
    
    const form = document.querySelector('form');
    form.parentNode.insertBefore(alert, form);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
      alert.style.animation = 'slideUp 0.5s ease-out';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  }

  // Add ripple effect to buttons
  document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 0.6s ease-out;
        pointer-events: none;
      `;
      
      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });

  // Add CSS animation for ripple
  const style = document.createElement('style');
  style.textContent = `
    @keyframes ripple {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
    @keyframes slideUp {
      to {
        opacity: 0;
        transform: translateY(-20px);
      }
    }
  `;
  document.head.appendChild(style);

  // Smooth scroll behavior
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Add keyboard shortcuts
  document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save form
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      document.querySelector('form').requestSubmit();
    }
    
    // Escape to close mobile sidebar
    if (e.key === 'Escape') {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.querySelector('.mobile-overlay');
      if (sidebar.classList.contains('mobile-open')) {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
      }
    }
  });
</script>
</body>
</html>