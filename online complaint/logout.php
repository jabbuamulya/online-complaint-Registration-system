<?php
/**
 * Citizen Logout Handler
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Unset user session
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);

set_flash('info', 'You have been successfully logged out. Thank you for using the Civic Complaint Portal.');
header('Location: ' . base_url('login.php'));
exit();
