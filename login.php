<?php

require_once('../../config.php');
require_once('auth.php');

$login = new auth_plugin_taat();
$login->authenticate_with_taat();

