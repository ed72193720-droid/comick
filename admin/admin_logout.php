<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/helpers.php';

destroySession();

redirect('admin/admin_login.php');
