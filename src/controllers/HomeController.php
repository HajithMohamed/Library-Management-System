<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;

class HomeController
{
    private $authHelper;

    public function __construct()
    {
        $this->authHelper = new AuthHelper();
    }

    /**
     * Show home page - main landing page
     */
    public function index()
    {
        // Check if user is logged in
        if ($this->authHelper->isLoggedIn()) {
            // Redirect logged-in users to their appropriate dashboard
            $this->authHelper->redirectByUserType();
            return;
        }

        // Show home page for non-logged-in users
        $this->render('home/index');
    }

    /**
     * Show about page
     */
    public function about()
    {
        $this->render('home/about');
    }

    /**
     * Show contact page
     */
    public function contact()
    {
        $this->render('home/contact');
    }

    /**
     * Show library information page
     */
    public function library()
    {
        $this->render('home/library');
    }

    /**
     * Render a view with data
     */
    private function render($view, $data = [])
    {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            http_response_code(404);
            include APP_ROOT . '/views/errors/404.php';
        }
    }
}
?>
