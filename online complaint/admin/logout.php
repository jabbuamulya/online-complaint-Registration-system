<?php
/**
 * Administrator Logout
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);

set_flash('info', 'Administrator logged out successfully.');
header('Location: ' . base_url('admin/login.php'));
exit();
