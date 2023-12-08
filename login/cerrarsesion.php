<?php
session_start();
include('../config.php');
session_destroy();
header($_ENV['URL_CLOSE_SESSION']);
