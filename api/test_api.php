<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/index.php';
$_GET['route'] = 'site';
require 'index.php';
