<?php
require_once __DIR__ . '/includes/functions.php';
$id=(int)($_GET['id']??0);
redirect('detail.php?id='.$id);
?>
