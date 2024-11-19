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

// Lấy danh sách lớp học
$classQuery = "SELECT class_id, class_name FROM Classes";
$classResult = $conn->query($classQuery);

// Lấy danh sách môn học
$subjectQuery = "SELECT subject_id, subject_name FROM Subjects";
$subjectResult = $conn->query($subjectQuery);

// Khởi tạo biến để lưu thông tin sinh viên
$students = [];

// Lấy giá trị từ form nếu có
$classId = isset($_POST['class_id']) ? $_POST['class_id'] : null;
$subjectId = isset($_POST['subject_id']) ? $_POST['subject_id'] : null;

// Xử lý điểm danh
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($classId && $subjectId) {
        // Lấy class_subject_id từ bảng ClassSubjects
        $classSubjectQuery = "SELECT class_subject_id FROM ClassSubjects WHERE class_id = '$classId' AND subject_id = '$subjectId'";
        $classSubjectResult = $conn->query($classSubjectQuery);
        $classSubjectId = null;

        if ($classSubjectResult && $classSubjectResult->num_rows > 0) {
            $classSubjectRow = $classSubjectResult->fetch_assoc();
            $classSubjectId = $classSubjectRow['class_subject_id'];
        } else {
            echo "<script>alert('Không tìm thấy lớp học hoặc môn học tương ứng! Vui lòng kiểm tra lại.');</script>";
        }

        // Lấy danh sách sinh viên trong lớp và môn học đã chọn
        $studentQuery = "
            SELECT Students.student_id, Users.user_id, Users.full_name, Users.email
            FROM Students
            JOIN Users ON Students.user_id = Users.user_id
            WHERE Students.class_id = '$classId'";
        
        $studentResult = $conn->query($studentQuery);

        if ($studentResult->num_rows > 0) {
            while ($row = $studentResult->fetch_assoc()) {
                $students[] = $row; // Lưu thông tin sinh viên vào mảng
            }
        }

        // Xử lý lưu điểm danh
        if (isset($_POST['attendance']) && $classSubjectId) {
            foreach ($_POST['attendance'] as $studentId => $status) {
                // Kiểm tra và đảm bảo trạng thái điểm danh hợp lệ
                $validStatuses = ['Có mặt', 'Nghỉ', 'Đi trễ'];
                if (!in_array($status, $validStatuses)) {
                    continue; // Bỏ qua nếu trạng thái không hợp lệ
                }

                $attendanceQuery = "
                    INSERT INTO Attendance (student_id, class_subject_id, date, status)
                    VALUES ('$studentId', '$classSubjectId', CURDATE(), '$status')
                    ON DUPLICATE KEY UPDATE status = '$status'"; // Cập nhật trạng thái nếu đã có điểm danh trước đó

                if ($conn->query($attendanceQuery) === TRUE) {
                    // Điểm danh thành công
                } else {
                    echo "Lỗi khi điểm danh: " . $conn->error;
                }
            }
            echo "<script>alert('Điểm danh thành công!');</script>";
        }
    } else {
        echo "<script>alert('Vui lòng chọn lớp học và môn học trước khi điểm danh!');</script>";
    }
}

// Lấy lại thông tin điểm danh đã lưu trong cơ sở dữ liệu
$attendanceStatuses = [];
if ($classId && $subjectId) {
    $classSubjectQuery = "SELECT class_subject_id FROM ClassSubjects WHERE class_id = '$classId' AND subject_id = '$subjectId'";
    $classSubjectResult = $conn->query($classSubjectQuery);
    if ($classSubjectResult->num_rows > 0) {
        $classSubjectRow = $classSubjectResult->fetch_assoc();
        $classSubjectId = $classSubjectRow['class_subject_id'];

        $attendanceQuery = "SELECT student_id, status FROM Attendance WHERE class_subject_id = '$classSubjectId' AND date = CURDATE()";
        $attendanceResult = $conn->query($attendanceQuery);
        while ($attendanceRow = $attendanceResult->fetch_assoc()) {
            $attendanceStatuses[$attendanceRow['student_id']] = $attendanceRow['status'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Điểm danh sinh viên</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
       body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    background-color: #f4f7f6;
}

.container {
    width: 80%;
    margin: auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #333;
    font-size: 2.5rem;
}

.form-container {
    margin: 20px 0;
    text-align: center;
}

select, button {
    padding: 10px;
    font-size: 1rem;
    margin: 10px 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    color: #333;
}

button {
    background-color: #5c6bc0;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #3f4b87;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}

th {
    background-color: #f2f2f2;
    color: #555;
    font-weight: bold;
}

tr:hover {
    background-color: #f9f9f9;
}

input[type="radio"] {
    margin-right: 10px;
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

.sideMenu a:hover {
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
    text-align: center;
}

@media screen and (max-width: 768px) {
    .container {
        width: 90%;
    }

    table {
        font-size: 0.9rem;
    }

    .sideMenu {
        width: 250px;
    }

    #content-area {
        margin-left: 250px;
    }
}
text-align: center;
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
    <a href="gv_index.php"><i class="fa fa-home"></i>Trang chủ</a>
    <a href="attdance.php" class="nav-link active text-black" style="background-color:#888888;"><i class="fa fa-check-circle"></i>Điểm danh</a>
    <a href="class+student.php"><i class="fa fa-users"></i>Lớp học và sinh viên</a>
    <a href="#"><i class='fas fa-chart-bar'></i>Thống kê điểm danh</a>
    <a href="../login-logout/logout.php"><i class="fa fa-sign-out"></i>Đăng xuất</a>
</div>
	</div>
	<div id="content-area">
		<span onclick="openNav()" style="font-size:30px;cursor:pointer">☰ Menu</span>
		<div class="content-text">
        <div class="container">
        <h1>Điểm danh sinh viên</h1>
        <div class="form-container">
            <form method="POST" action="">
                <label for="class_id">Chọn lớp học:</label>
                <select name="class_id" id="class_id" required>
                    <option value="">-- Chọn lớp học --</option>
                    <?php while ($classRow = $classResult->fetch_assoc()) { ?>
                        <option value="<?php echo $classRow['class_id']; ?>" <?php echo ($classRow['class_id'] == $classId) ? 'selected' : ''; ?>><?php echo $classRow['class_name']; ?></option>
                    <?php } ?>
                </select>

                <label for="subject_id">Chọn môn học:</label>
                <select name="subject_id" id="subject_id" required>
                    <option value="">-- Chọn môn học --</option>
                    <?php while ($subjectRow = $subjectResult->fetch_assoc()) { ?>
                        <option value="<?php echo $subjectRow['subject_id']; ?>" <?php echo ($subjectRow['subject_id'] == $subjectId) ? 'selected' : ''; ?>><?php echo $subjectRow['subject_name']; ?></option>
                    <?php } ?>
                </select>

                <button type="submit">Lấy danh sách sinh viên</button>
            </form>
        </div>

        <?php if (!empty($students)) { ?>
            <form method="POST" action="">
                <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $subjectId; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Mã sinh viên</th>
                            <th>Tên sinh viên</th>
                            <th>Email</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) { ?>
                            <tr>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['email']; ?></td>
                                <td>
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Có mặt" <?php echo (isset($attendanceStatuses[$student['student_id']]) && $attendanceStatuses[$student['student_id']] == 'Có mặt') ? 'checked' : ''; ?>> Có mặt
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Nghỉ" <?php echo (isset($attendanceStatuses[$student['student_id']]) && $attendanceStatuses[$student['student_id']] == 'Nghỉ') ? 'checked' : ''; ?>> Nghỉ
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Đi trễ" <?php echo (isset($attendanceStatuses[$student['student_id']]) && $attendanceStatuses[$student['student_id']] == 'Đi trễ') ? 'checked' : ''; ?>> Đi trễ
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <br>
                <button type="submit">Điểm danh</button>
            </form>
        <?php } ?>
    </div>
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
