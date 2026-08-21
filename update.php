<?php
session_start();

$ids = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ids > 0) {
    header('Location: admin/candidate_edit.php?id=' . $ids);
} else {
    header('Location: admin/candidates.php');
}
exit();
?>