<?php
session_start();
include('../config.php');
require_once dirname(__DIR__) . '/config/ConfigUrl.php';
$baseUrl = ConfigUrl::get();
session_destroy();
header("Location: " . $baseUrl . 'login/index.php');
