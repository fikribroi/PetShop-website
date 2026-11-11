<?php
/**
 * Admin Logout Processor
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

logout_user(base_url('public/login.php'));

