<?php
session_start();
session_unset(); // Hủy tất cả các biến phiên
session_destroy(); // Hủy phiên
header("Location:login.php"); // Chuyển hướng về trang đăng nhập
exit();
?>