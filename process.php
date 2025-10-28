<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'api/config.php';

// Procesa los datos y realiza las operaciones necesarias
// ...

// Redirige de vuelta a index.php después de procesar
header('Location: index.php');
exit();
?>