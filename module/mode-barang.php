<?php
if (userLogin()['level'] != 1) {
    header("location:" . $main_url . "error-page.php");
    exit();
}

function generateId()
{
    global $koneksi;

    $queryId = mysqli_query($koneksi, "SELECT max(id_barang) as maxid FROM tbl_barang");
    $data = mysqli_fetch_array($queryId);

    $maxid = $data['maxid'] ?? 'BRG-000'; // Default value if null
    
    $noUrut = (int) substr($maxid, 4, 3); // Extract the numeric part safely
    $noUrut++;
    $maxid = "BRG-" . sprintf("%03s", $noUrut); // Format the new ID

    return $maxid;
}

function insert($data)
{

    global $koneksi;

    $id = mysqli_real_escape_string($koneksi, $data['kode']);
    $barcode = mysqli_real_escape_string($koneksi, $data['barcode']);
    $name = mysqli_real_escape_string($koneksi, $data['nama_barang']);
    $satuan = mysqli_real_escape_string($koneksi, $data['satuan']);
    $harga_beli = mysqli_real_escape_string($koneksi, $data['harga_beli']);
    $harga_jual = mysqli_real_escape_string($koneksi, $data['harga_jual']);
    $stockmin = mysqli_real_escape_string($koneksi, $data['stock_minimal']);
    $gambar = mysqli_real_escape_string($koneksi, $_FILES['image']['name']);

    $cekBarcode = mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE barcode = '$barcode'");
    if (mysqli_num_rows($cekBarcode)) {
        echo "<script>alert('Kode barcode sudah ada, barang gagal ditambahkan')</script>";
        return false;
    }

    if ($gambar != null) {
        $gambar = uploadimg(null, $id);
    } else {
        $gambar = 'default-brg.png';
    }

    //gambar tidak sesuai validasi
    if ($gambar == '') {
        return false;
    }

    $sqlBarang = "INSERT INTO tbl_barang VALUE ('$id', '$barcode', '$name', '$harga_beli', '$harga_jual', 0, '$satuan', '$stockmin', '$gambar')";
    mysqli_query($koneksi, $sqlBarang);
    return mysqli_affected_rows($koneksi);
}

function delete($id, $gbr)
{
    global $koneksi;

    $sqlDel = "DELETE FROM tbl_barang WHERE id_barang = '$id'";
    mysqli_query($koneksi, $sqlDel);
    if ($gbr != 'default-brg.png') {
        unlink('../assets/image/' . $gbr);
    }
    return mysqli_affected_rows($koneksi);
}

function selectUser1($level)
{
    $result = null;
    if ($level == 1) {
        $result = "selected";
    }
    return $result;
}
function selectUser2($level)
{
    $result = null;
    if ($level == 2) {
        $result = "selected";
    }
    return $result;
}
function selectUser3($level)
{
    $result = null;
    if ($level == 3) {
        $result = "selected";
    }
    return $result;
}

function update($data)
{
    global $koneksi;

    $iduser = mysqli_real_escape_string($koneksi, $data['id']);
    $username = strtolower(mysqli_real_escape_string($koneksi, $data['username']));
    $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
    $level = mysqli_real_escape_string($koneksi, $data['level']);
    $address = mysqli_real_escape_string($koneksi, $data['address']);
    $gambar = mysqli_real_escape_string($koneksi, $_FILES['image']['name']);
    $fotoLama = mysqli_real_escape_string($koneksi, $data['oldImg']);

    // cek username sekarang
    $queryUsername = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE userid = '$iduser'");
    $dataUsername = mysqli_fetch_assoc($queryUsername);
    $curUsername = $dataUsername['username'];

    // cek username baru
    $newUsername = mysqli_query($koneksi, "SELECT username FROM tbl_user WHERE username = '$username'");

    if ($username !== $curUsername) {
        if (mysqli_num_rows($newUsername)) {
            echo "<script>alert('Username sudah terpakai, update data user gagal !');
        document.location.href = 'data-user.php';
        </script>";
            return false;
        }
    }

    // cek gambar
    if ($gambar != null) {
        $url = "data-user.php";
        $imgUser = uploadimg($url);
        if ($fotoLama != 'default.png') {
            @unlink('../assets/image/' . $fotoLama);
        }
    } else {
        $imgUser = $fotoLama;
    }
    mysqli_query($koneksi, "UPDATE tbl_user SET username = '$username', fullname = '$fullname', address = '$address', level = '$level', foto = '$imgUser' WHERE userid = '$iduser'");

    return mysqli_affected_rows($koneksi);
}