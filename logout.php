<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
unset($_SESSION['id_member'], $_SESSION['nm_member'], $_SESSION['member_foto'], $_SESSION['member_logged_in']);
header('Location: index.php');
exit;
?>
