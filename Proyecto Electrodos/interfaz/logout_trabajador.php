<?php
session_start();
session_destroy();
header("Location: http://192.168.101.92:8084/tp/login/login.php");
exit;
