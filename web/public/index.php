<?php
require_once __DIR__ . '/../includes/auth.php';
if (is_logged_in()) {
    redirect(role_dashboard(current_role()));
}
redirect('login.php');
