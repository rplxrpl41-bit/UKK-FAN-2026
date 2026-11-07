<?php
session_start();
$_SESSION['login'] = true;
$_SESSION['id'] = 104;
$_SESSION['name'] = 'administrator';
$_SESSION['email'] = 'admin@gmail.com';
$_SESSION['role'] = 'admin';
echo 'session-ready';
