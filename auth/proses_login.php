<?php

session_start();

include '../config/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query =
mysqli_query(
$koneksi,
"SELECT * FROM users
WHERE email='$email'"
);

$user =
mysqli_fetch_assoc($query);

if(
$user &&
password_verify(
$password,
$user['password']
)
){

    $_SESSION['id'] =
    $user['id'];

    $_SESSION['nama'] =
    $user['nama'];

    $_SESSION['role'] =
    $user['role'];

    if(
    $user['role']
    == 'admin'
    ){

        header(
        "Location: ../admin/dashboard.php"
        );

    }else{

        header(
        "Location: ../user/dashboard.php"
        );

    }

}else{

    echo "
    <script>

    alert('Login Gagal');

    window.location='login.php';

    </script>
    ";

}
?>