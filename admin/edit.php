<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "thaidaddy";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login-logout/login.php");
    exit();
}
$user = $_SESSION['user']; // This should be the logged-in user's ID or identifier
$userQuery = "SELECT full_name FROM Users WHERE email = '$user'"; // Modify to your login identifier if it's not 'email'
$userResult = $conn->query($userQuery);
$fullName = "";

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $fullName = $userRow['full_name'];
}
// Lấy ID người dùng từ POST
$userId = $_POST['user_id'] ?? null;
$userData = [];
$studentData = [];
$classes = [];

if ($userId) {
    // Lấy thông tin người dùng
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    if ($userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();
    } else {
        echo "Không tìm thấy người dùng.";
        exit();
    }
    $stmt->close();

    // Lấy thông tin sinh viên nếu role là Sinh viên
    if ($userData['role'] === 'Sinh viên') {
        $stmt = $conn->prepare("SELECT * FROM Students WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $studentResult = $stmt->get_result();
        if ($studentResult->num_rows > 0) {
            $studentData = $studentResult->fetch_assoc();
        }
        $stmt->close();

        // Lấy danh sách lớp học
        $classQuery = "SELECT class_id, class_name FROM Classes";
        $classResult = $conn->query($classQuery);
        while ($row = $classResult->fetch_assoc()) {
            $classes[] = $row;
        }
    }
} else {
    echo "Không tìm thấy người dùng.";
    exit();
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $fullName = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $role = htmlspecialchars(trim($_POST['role']));

    if (!$email) {
        echo "<script>alert('Email không hợp lệ.'); window.history.back();</script>";
        exit();
    }

    if ($role === 'Sinh viên') {
        $classId = intval($_POST['class_id']);

        // Cập nhật thông tin người dùng và sinh viên
        $stmtUser = $conn->prepare("UPDATE Users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
        $stmtUser->bind_param("sssi", $fullName, $email, $role, $userId);

        $stmtStudent = $conn->prepare("UPDATE Students SET class_id = ? WHERE user_id = ?");
        $stmtStudent->bind_param("ii", $classId, $userId);

        $success = $stmtUser->execute() && $stmtStudent->execute();
        $stmtUser->close();
        $stmtStudent->close();
    } else {
        // Cập nhật chỉ thông tin người dùng
        $stmt = $conn->prepare("UPDATE Users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $fullName, $email, $role, $userId);
        $success = $stmt->execute();
        $stmt->close();
    }

    if ($success) {
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location.href='information.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật thông tin.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sửa thông tin người dùng</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
}
.sideMenu {
	height: 100%;
	width: 0;
	position: fixed;
	z-index: 1;
	top: 0;
	left: 0;
	background:  #0c787d;
	overflow-x: hidden;
	transition: 0.5s;
	padding-top: 60px;
}
.main-menu h2 {
	text-align: center;
	letter-spacing: 7px;
	color: #fff;
	background: #111;
	padding: 20px 0;
}
.sideMenu a {
	padding: 8px 8px 8px 32px;
	text-decoration: none;
	color: #fff;
	display: block;
	transition: 0.3s;
	font-size: 18px;
	margin-bottom: 20px;
	text-transform: uppercase;
	
}
.sideMenu a i {
	padding-right: 15px;
}
.main-menu a:hover {
	color: #f1f1f1;
	background: #BBBBBB;
}
.sideMenu .closebtn {
	position: absolute;
	top: 0;
	right: 25px;
	font-size: 36px;
	margin-left: 50px;
}
#content-area {
	transition: margin-left .5s;
	padding: 16px;
}
.content-text {
	padding: 100px 180px;
	text-align: center;
}
.content-text h2 {
	background: darksalmon;
	display: inline-block;
	padding: 15px 35px;
	text-transform: uppercase;
	font-size: 50px;
	color: #fff;
}
.content-text h3 {
	text-transform: uppercase;
	font-size: 45px;
	margin: 0;
	letter-spacing: 3px;
}
                                         
            
    </style>    
</head>
<body>
    <div class="sideMenu" id="side-menu">
        
		<a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
		<div class="main-menu">
    <h2>
    <?php echo $fullName; ?>
    </h2>
    <a href="index.php"class="nav-link active text-black" style="background-color:#888888;"><i class="fa fa-home"></i>Trang chủ</a>
    <a href="information.php"><i class="fa fa-users"></i>Thông tin người dùng</a>
    <a href="#"><i class="fa fa-calendar"></i>Thông tin học kỳ</a>
    <a href="#"><i class="fa fa-building"></i>Thông tin lớp học</a>
    <a href="#"><i class="fa fa-check-circle"></i>Thông tin điểm danh</a>
    <a href="../login-logout/logout.php"><i class="fa fa-sign-out"></i>Đăng xuất</a>
</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
		
		<div class="content-text">
			
        <h1>Sửa thông tin người dùng</h1>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userData['user_id']); ?>">
        
        <label for="full_name">Họ và Tên:</label>
        <input type="text" name="full_name" id="full_name" placeholder="Họ và Tên" value="<?php echo htmlspecialchars($userData['full_name']); ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" placeholder="Email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
        
        <label for="role">Vai trò:</label>
        <select name="role" id="role" required>
            <option value="Giáo vụ" <?php if ($userData['role'] === 'Giáo vụ') echo 'selected'; ?>>Giáo vụ</option>
            <option value="Giáo viên" <?php if ($userData['role'] === 'Giáo viên') echo 'selected'; ?>>Giáo viên</option>
            <option value="Sinh viên" <?php if ($userData['role'] === 'Sinh viên') echo 'selected'; ?>>Sinh viên</option>
        </select>

        <?php if ($userData['role'] === 'Sinh viên' && !empty($classes)): ?>
            <label for="class_id">Chọn lớp:</label>
            <select name="class_id" id="class_id" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['class_id']; ?>" <?php if ($studentData['class_id'] == $class['class_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($class['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit" name="update">Cập nhật</button>
    </form>
		</div>
	</div>
	<script>
	function openNav() {
	 document.getElementById("side-menu").style.width = "300px";
	 document.getElementById("content-area").style.marginLeft = "300px"; 
	}

	function closeNav() {
	 document.getElementById("side-menu").style.width = "0";
	 document.getElementById("content-area").style.marginLeft= "0";  
	}
	</script> 
</body>
</html>
