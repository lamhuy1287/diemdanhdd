<?php
$user_id = $_POST['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$database = "thaidaddy";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $userId = $conn->real_escape_string($_POST['user_id']); // Sử dụng real_escape_string để bảo mật

    // Xóa thông tin trong bảng Students
    $deleteStudentQuery = "DELETE FROM Students WHERE user_id = '$userId'";
    $conn->query($deleteStudentQuery);

    // Xóa thông tin trong bảng Users
    $deleteUserQuery = "DELETE FROM Users WHERE user_id = '$userId'"; // Đã sửa lỗi tên biến
    if ($conn->query($deleteUserQuery) === TRUE) {
        // Nếu xóa thành công, chuyển hướng về information.php
        header("Location: information.php");
        exit();
    } else {
        // Nếu có lỗi, chuyển hướng về information.php với thông báo lỗi
        header("Location: information.php" . $conn->error);
        exit();
    }
} else {
    // Nếu không có ID người dùng nào được cung cấp, chuyển hướng về information.php
    header("Location: information.php");
    exit();
}

?>