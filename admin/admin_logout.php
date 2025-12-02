<?php
require __DIR__ . '/../includes/session.php';

destroySession();

redirect('admin_login.php');
