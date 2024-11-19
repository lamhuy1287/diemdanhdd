<?php
session_start();
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $servername = "localhost";
    $username = "root";
    $db_password = ""; // Mật khẩu kết nối đến MySQL
    $database = "thaidaddy"; // Tên cơ sở dữ liệu

    // Kết nối đến CSDL
    $conn = mysqli_connect($servername, $username, $db_password, $database);

    // Kiểm tra kết nối
    if (!$conn) {
        die("Kết nối thất bại: " . mysqli_connect_error());
    }

    // Kiểm tra email và mật khẩu
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc(); // Lấy thông tin người dùng
        $_SESSION['user'] = $email;

        // Kiểm tra vai trò và chuyển hướng
        if ($user['role'] === 'Admin') {
            header("Location:../admin/index.php");
        } else {
            header("Location:../giaoVien/gv_index.php ");
        }
        exit();
    } else {
        $loginError = "Email hoặc mật khẩu không chính xác!";
    }

    // Đóng kết nối
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>
    login
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <style>
    @import url("https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400");

body,
html {
  font-family: "Source Sans Pro", sans-serif;
  background: #2ebf91;
  padding: 0;
  margin: 0;
}

.container {
  text-align: center;
  background: #2c3338;
  border-radius: 9px;
  border-top: 10px solid #0c787d;
  border-bottom: 10px solid #0c787d;
  width: 400px;
  height: 500px;
}

.box h4 {
  font-family: "Source Sans Pro", sans-serif;
  color:#129aa1;
  font-size: 20px;
  margin-top: 94px;
}

.box h4 span {
  color: #dfdeee;
  font-weight: lighter;
}

.box h5 {
  font-family: "Source Sans Pro", sans-serif;
  font-size: 13px;
  color: #a1a4ad;
  letter-spacing: 1.5px;
  margin-top: -15px;
  margin-bottom: 70px;
}

.box input[type="text"],
.box input[type="password"] {
  display: block;
  margin: 20px auto;
  background-color: #3b4148;
  border: 0;
  border-radius: 5px;
  padding: 14px 10px;
  width: 320px;
  outline: 0;
  color: #a9a9a9;
  -webkit-transition: all 0.2s ease-out;
  -moz-transition: all 0.2s ease-out;
  -ms-transition: all 0.2s ease-out;
  -o-transition: all 0.2s ease-out;
  transition: all 0.2s ease-out;
}
::-webkit-input-placeholder {
  color: #565f79;
}

.box input[type="text"]:focus,
.box input[type="password"]:focus {
  border: 1px solid #79a6fe;
}

a {
  color:#129aa1;
  text-decoration: none;
}
.btn1 {
  border: 0;
  background: #0c787d;
  color: #dfdeee;
  border-radius: 7px;
  width: 340px;
  height: 49px;
  font-size: 16px;
  transition: 0.3s;
  cursor: pointer;
  margin-top: 20px;
}

.btn1:hover {
  background: #129aa1;
}

.forgetpass {
  position: relative;
  float: right;
  right: 28px;
}

  </style>
</head>
<body style="display: flex; justify-content:center ; align-items: center;height: 100vh;overflow: hidden;">
    <div class="container">
      <form name="form1" class="box" method="post">
        <h4>Login</span></h4>
        <h5>Sign in to your account.</h5>
        <input type="text" name="email" placeholder="Email" autocomplete="off" />
        <input type="password" name="password" placeholder="Passsword" id="pwd" autocomplete="off" />
        <input type="submit" value="Sign in" class="btn1" />
      </form>
    </div>
</body>
</html>
