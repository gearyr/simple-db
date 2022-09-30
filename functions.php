<?php 

$conn = mysqli_connect("localhost","root", "", "phpdasar");

function query($query){
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while( $row = mysqli_fetch_assoc($result) ){
        $rows[] = $row;
    }
    return $rows;
}



function tambah($data){
    global $conn;
    
    $nrp = htmlspecialchars($data["nrp"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);

    // upload gambar
    $gambar = upload();
    if(!$gambar){
        return false;
    }

    $query = "INSERT INTO mahasiswa VALUES
            ('','$nama','$nrp','$email','$jurusan','$gambar')
         ";

    mysqli_query($conn,$query);

    return mysqli_affected_rows($conn);
}

function upload(){
    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmpName = $_FILES['gambar']['tmp_name'];
    
    // cek apakah ada gambar yg diupload
    if($error == 4){
        echo "<script>
                alert('Pilih gambar terlebih dahulu!')
        </script>";
        return false;
    }

    // cek apakah filenya adalah gambar
    $ekstensiGambarValid = ["png","jpg","jpeg"];
    $ekstensiGambar = explode('.',$namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if ( !in_array($ekstensiGambar,$ekstensiGambarValid) ){
        echo "<script>
                alert('yang anda upload bukan gambar!')
        </script>";
        return false;
    }

    // cek jika ukurannya terlalu besar
    if( $ukuranFile > 1000000 ){
        echo "<script>
                alert('ukuran gambar terlalu besar!')
        </script>";
        return false;
    }

    // lolos pengecekan, gambar siap diupload
    // generate nama baru
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;


    move_uploaded_file($tmpName,'img/'.$namaFileBaru);

    return $namaFileBaru;

}


function hapus($id) {
    global $conn;
    mysqli_query($conn,"DELETE FROM mahasiswa WHERE id = $id");

    return mysqli_affected_rows($conn);
}

function ubah($data){
    global $conn;
    
    $id = $data["id"];
    $nrp = htmlspecialchars($data["nrp"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);
    $gambarLama = ($data["gambarLama"]);

    if( $_FILES["gambar"]["error"] === 4 ){
        $gambar = $gambarLama;
    } else{
        $gambar = upload();
    }

    $query = "UPDATE mahasiswa SET
                nrp = '$nrp',
                nama = '$nama',
                email = '$email',
                jurusan = '$jurusan',
                gambar = '$gambar'
                WHERE id = $id
            ";

    mysqli_query($conn,$query);

    return mysqli_affected_rows($conn);
}

function cari($keyword){
    $query = "SELECT * FROM mahasiswa WHERE 
                nama LIKE '%$keyword%' OR
                nrp LIKE '%$keyword%' OR
                email LIKE '%$keyword%' OR
                jurusan LIKE '%$keyword%'";
    return query($query);
}

function register($data){
    global $conn;
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn,$data["password"]);
    $password2 = mysqli_real_escape_string($conn,$data["password2"]);

    // cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");

    if( mysqli_fetch_assoc($result) ){
        echo "<script>
                alert('username sudah terdaftar')
        </script>";
        return false;
    }    


    // cek konfirmasi password
    if( $password !== $password2 ){
        echo "<script>
                alert('konfirmasi password salah')
        </script>";
        return false;
    }

    //enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    //tambahkan user baru ke database
    mysqli_query($conn,"INSERT INTO user VALUES('','$username','$password')");

    return mysqli_affected_rows($conn);
}

?>