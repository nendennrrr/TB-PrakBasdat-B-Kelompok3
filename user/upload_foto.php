<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id'];

if(!isset($_FILES['foto']) || $_FILES['foto']['error'] != 0){
    header("Location: profil.php?msg=error");
    exit;
}

$file = $_FILES['foto'];

/* VALIDASI */
$allowed = ['jpg','jpeg','png','webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if(!in_array($ext, $allowed)){
    header("Location: profil.php?msg=invalid");
    exit;
}

if($file['size'] > 2 * 1024 * 1024){
    header("Location: profil.php?msg=large");
    exit;
}

/* ✔ FOLDER ASSETS KAMU */
$folder = "../assets/img/";

if(!is_dir($folder)){
    mkdir($folder, 0777, true);
}

/* AMBIL FOTO LAMA */
$get = mysqli_query($koneksi,"SELECT foto FROM users WHERE id=$id_user");
$data = mysqli_fetch_assoc($get);

/* NAMA FILE */
$newName = "profile_".$id_user."_".time().".".$ext;

/* UPLOAD */
if(!move_uploaded_file($file['tmp_name'], $folder.$newName)){
    header("Location: profil.php?msg=uploadfail");
    exit;
}

/* HAPUS FOTO LAMA */
if(!empty($data['foto'])){
    $oldFile = $folder.$data['foto'];
    if(file_exists($oldFile)){
        unlink($oldFile);
    }
}

/* UPDATE DB */
mysqli_query($koneksi,"
    UPDATE users
    SET foto='$newName'
    WHERE id=$id_user
");

header("Location: profil.php?msg=success");
exit;