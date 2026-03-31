<?php

require_once __DIR__ . '/app/auth.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

redirect('login.php');

