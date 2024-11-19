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
if (!isset($_SESSION['user'])) {
    header("Location: login-logout/login.php");
    exit();
}
$user = $_SESSION['user'];
$userQuery = "SELECT full_name FROM Users WHERE email = '$user'";
$userResult = $conn->query($userQuery);
$fullName = "";

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $fullName = $userRow['full_name'];
}

// Lấy thông tin lớp học
$classQuery = "SELECT * FROM Classes"; // Giả sử bạn có bảng Classes
$classResult = $conn->query($classQuery);

// Lấy thông tin giáo vụ
$GiaoVuQuery = "SELECT * FROM Users WHERE role='Giáo vụ'";
$GiaoVuResult = $conn->query($GiaoVuQuery);

// Lấy thông tin giáo viên
$GiaoVienQuery = "SELECT * FROM Users WHERE role='Giáo viên'";
$GiaoVienResult = $conn->query($GiaoVienQuery);

// Lấy thông tin sinh viên
$SinhVienQuery = "SELECT 
    Students.student_id, 
    Users.user_id,
    Users.full_name, 
    Users.email, 
    Users.role, 
    Classes.class_name
FROM Students 
JOIN Users ON Students.user_id = Users.user_id
JOIN Classes ON Students.class_id = Classes.class_id
WHERE Users.role = 'Sinh viên'";
$SinhVienResult = $conn->query($SinhVienQuery);


// Xử lý thêm, sửa, xóa thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Retrieve form data
        $fullName = $_POST['full_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        $classId = isset($_POST['class_id']) ? $_POST['class_id'] : null;

        // Check if the email already exists
        $checkEmailQuery = "SELECT * FROM Users WHERE email = '$email'";
        $checkEmailResult = $conn->query($checkEmailQuery);

        if ($checkEmailResult->num_rows > 0) {
            echo "<script>alert('Email đã tồn tại! Vui lòng sử dụng một email khác.');</script>";
        } else {
            // Insert the user into the Users table
            $insertQuery = "INSERT INTO Users (full_name, email, password, role) VALUES ('$fullName', '$email', '$password', '$role')";
            if ($conn->query($insertQuery)) {
                // If the user is a student, add to the Students table
                if ($role == 'Sinh viên' && $classId) {
                    $userId = $conn->insert_id; // Get the ID of the newly inserted user
                    $insertStudentQuery = "INSERT INTO Students (user_id, class_id) VALUES ('$userId', '$classId')";
                    $conn->query($insertStudentQuery);
                }
                echo "<script>alert('Thêm người dùng thành công!');</script>";
            } else {
                echo "<script>alert('Lỗi khi thêm người dùng.');</script>";
            }
        }
    }
}


?>
<!DOCTYPE html>
<html>

<head>
    <title>admin</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family= Roboto:wght@400;700&display=swap" rel="stylesheet">
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
            background: #0c787d;
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

        h1 {
            text-align: center;
        }

        .content-text {
            padding: 0px 20px;
            /* Giảm padding để bảng có thể vừa khung hình */
            text-align: center;
        }

        table {
            width: 100%;
            /* Đặt chiều rộng của bảng là 100% */
            border-collapse: collapse;
            /* Gộp các viền ô lại với nhau */
            margin: 20px 0;
            /* Thêm khoảng cách phía trên và dưới bảng */
        }

        table,
        th,
        td {
            border: 2px solid #000;
            /* Đặt viền cho bảng và các ô */
        }

        th,
        td {
            padding: 12px;
            /* Thêm khoảng cách bên trong ô */
            text-align: center;
            /* Căn giữa nội dung ô */
        }

        th {
            background-color: #f2f2f2;
            /* Màu nền cho tiêu đề bảng */
        }

        .toc {
            margin: 20px 0;
            /* Khoảng cách cho mục lục */
            text-align: center;
            /* Căn giữa mục lục */
        }

        .toc a {
            margin: 0 15px;
            /* Khoảng cách giữa các liên kết */
            text-decoration: none;
            /* Bỏ gạch chân */
            color: #0c787d;
            /* Màu chữ cho mục lục */
            font-weight: bold;
            /* Chữ đậm */
        }

        .form-container {
            margin: 20px 0;
            /* Khoảng cách cho form */
            text-align: center;
            /* Căn giữa form */
        }
    </style>
    <script>
        function toggleClassSelect(role) {
            const classSelect = document.getElementById('class-select');
            if (role === 'Sinh viên') {
                classSelect.style.display = 'block';
            } else {
                classSelect.style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <div class="sideMenu" id="side-menu">
        <a class="closebtn" href="javascript:void(0)" onclick="closeNav()">×</a>
        <div class="main-menu">
            <h2>
                <?php echo $fullName; ?>
            </h2>
            <a href="index.php"><i class="fa fa-home"></i>Trang chủ</a>
            <a href="information.php" class="nav-link active text-black" style="background-color:#888888;"><i class="fa fa-users"></i>Thông tin người dùng</a>
            <a href="#"><i class="fa fa-calendar"></i>Thông tin học kỳ</a>
            <a href="#"><i class="fa fa-building"></i>Thông tin lớp học</a>
            <a href="#"><i class="fa fa-check-circle"></i>Thông tin điểm danh</a>
            <a href="../login-logout/logout.php"><i class="fa fa-sign-out"></i>Đăng xuất</a>
        </div>
    </div>
    <div id="content-area">
        <span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
        <h1>Thông tin người dùng</h1>
        <div class="toc">
            <a href="#giao-vu">Thông tin giáo vụ</a>
            <a href="#giao-vien">Thông tin giáo viên</a>
            <a href="#sinh-vien">Thông tin sinh viên</a>
        </div>
        <hr>

        <div class="form-container">
            <h2>Thêm thông tin người dùng</h2>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Họ và Tên" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <select name="role" onchange="toggleClassSelect(this.value)" required>
                    <option value="Giáo vụ">Giáo vụ</option>
                    <option value="Giáo viên">Giáo viên</option>
                    <option value="Sinh viên">Sinh viên</option>
                </select>
                
                <div id="class-select" style="display:none;">
                    <select name="class_id">
                        
                        <option value="">Chọn lớp</option>
                        <?php
                        if ($classResult->num_rows > 0) {
                            while ($classRow = $classResult->fetch_assoc()) {
                                echo "<option value='" . $classRow['class_id'] . "'>" . $classRow['class_name'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <br>
                <br>
                <button type="submit" name="add">Thêm</button>
            </form>
        </div>

        <div class="content-text">
            <h2 id="giao-vu"><u>Thông tin giáo vụ</u></h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
                <?php
if ($GiaoVuResult->num_rows > 0) {
    while ($row = $GiaoVuResult->fetch_assoc()) {
        $user_id = $row["user_id"]; // Lấy giá trị id
        echo "<tr>
                <td>" . $row['user_id'] . "</td>
                <td>" . $row['full_name'] . "</td>
                <td>" . $row['email'] . "</td>
                <td>" . $row['role'] . "</td>
                <td style='margin-left:15px;'>
                    <form method='POST' action='edit.php' style='display:inline;'>
                        <input name='user_id' value=' $user_id' type='hidden'>
                        <button type='submit' class='btn btn-danger m-2'>Edit</button>
                    </form>
                    <form method='POST' action='delete.php' style='display:inline;'>
                        <input name='user_id' value=' $user_id' type='hidden'>
                        <button type='submit' class='btn btn-danger m-2'>Delete</button>
                    </form>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5'>Không có thông tin giáo vụ nào.</td></tr>";
}
?>

            </table>
            <hr>
            <h2 id="giao-vien"><u>Thông tin giáo viên</u></h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
                <?php
                if ($GiaoVienResult->num_rows > 0) {
                    while ($row = $GiaoVienResult->fetch_assoc()) {
                        $user_id = $row["user_id"];
                        echo "<tr>
                                <td>" . $row['user_id'] . "</td>
                                <td>" . $row['full_name'] . "</td>
                                <td>" . $row['email'] . "</td>
                                <td>" . $row['role'] . "</td>
                                <td style='margin-left:15px;'>
                    <form method='POST' action='edit.php' style='display:inline;'>
                        <input name='user_id' value=' $user_id' type='hidden'>
                        <button type='submit' class='btn btn-danger m-2'>Edit</button>
                    </form>
                    <form method='POST' action='delete.php' style='display:inline;'>
                        <input name='user_id' value=' $user_id' type='hidden'>
                        <button type='submit' class='btn btn-danger m-2'>Delete</button>
                    </form>
                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Không có thông tin giáo viên nào.</td></tr>";
                }
                ?>
            </table>
            <hr>
            <h2 id="sinh-vien"><u>Thông tin sinh viên</u></h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>ID Sinh viên</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Lớp</th>
                    <th>Hành động</th>
                </tr>
                <?php
                if ($SinhVienResult->num_rows > 0) {
                    while ($row = $SinhVienResult->fetch_assoc()) {
                        $user_id = $row["user_id"];
                        echo "<tr>
                                <td>" . $row['user_id'] . "</td>
                                <td>" . $row['student_id'] . "</td>
                                <td>" . $row['full_name'] . "</td>
                                <td>" . $row['email'] . "</td>
                                <td>" . $row['role'] . "</td>
                                <td>" . $row['class_name'] . "</td>
                                 <td style='margin-left:15px;'>
                    <form method='POST' action='edit.php' style='display:inline;'>
                        <input name='user_id' value=' $user_id' type='hidden'>
                        <button type='submit' class='btn btn-danger m-2'>Edit</button>
                    </form>
                    <form method='POST' action='delete.php' style='display:inline;'>
                        <input name='user_id' value=' $user_id' type='hidden'>
                        <button type='submit' class='btn btn-danger m-2'>Delete</button>
                    </form>
                </td>
                                
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Không có thông tin sinh viên nào.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
    <script>
        function openNav() {
            document.getElementById("side-menu").style.width = "300px";
            document.getElementById("content-area").style.marginLeft = "300px";
        }

        function closeNav() {
            document.getElementById("side-menu").style.width = "0";
            document.getElementById("content-area").style.marginLeft = "0";
        }
    </script>
</body>

</html>