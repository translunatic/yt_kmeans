<?php
//Koneksi Basis Data
$mysqli=new mysqli("localhost","root","","db_mahasiswa_kmeans");
//Cek Koneksi
if (mysqli_connect_errno()){
	echo "Tidak terhubung";
	exit;
}

//Fungsi mencari kueri single data
function caridata($mysqli,$query){
	$row= $mysqli->query($query)->fetch_array();
	return $row[0];
}

//Inisialisasi Cluster Awal
$jumlahmahasiswa=caridata($mysqli,"select count(*) from tb_mahasiswa");
for ($i=0;$i<$jumlahmahasiswa; $i++) { 
	$clusterawal[$i]="1";
}

//Set Default Nilai Centroid 1,2,3
$centro1[0] = array('4','2','3');
$centro2[0] = array('2','3','2');
$centro3[0] = array('2','1','3');


$status='false';
$loop='0';
$x=0;
while ($status=='false'){

	//Proses K-Means Perhitungan
	$query="select * from tb_mahasiswa";
	$result=$mysqli->query($query);
	while ($data=mysqli_fetch_assoc($result)) {
		extract($data);
		$hasilc1=0;
		$hasilc2=0;
		$hasilc3=0;

		//Proses Pencarian Nilai Centro 1
		$hasilc1=sqrt(pow($tanggungan-$centro1[$loop][0],2) +
			pow($k_pekerjaan-$centro1[$loop][1],2) + 
			pow($k_penghasilan-$centro1[$loop][2],2));

		//Proses Pencarian Nilai Centro 2
		$hasilc2=sqrt(pow($tanggungan-$centro2[$loop][0],2) +
			pow($k_pekerjaan-$centro2[$loop][1],2) + 
			pow($k_penghasilan-$centro2[$loop][2],2));

		//Proses Pencarian Nilai Centro 3
		$hasilc3=sqrt(pow($tanggungan-$centro3[$loop][0],2) +
			pow($k_pekerjaan-$centro3[$loop][1],2) + 
			pow($k_penghasilan-$centro3[$loop][2],2));

		//Mencari Nilai Terkecil
		if ($hasilc1<$hasilc2 && $hasilc1<$hasilc3){
			$clusterakhir[$x]='C1';
			update_mahasiswa($mysqli,$idmhs,'C1');

		}else if($hasilc2<$hasilc1 && $hasilc2<$hasilc3){
			$clusterakhir[$x]='C2';
			update_mahasiswa($mysqli,$idmhs,'C2');

		}else{
			$clusterakhir[$x]='C3';
			update_mahasiswa($mysqli,$idmhs,'C3');

		}
		//Penambhan Counter Index
		$x+=1;



	}

	$loop+=1;
	//Proses mencari centroid baru ambil dari basis data.
	//Centroid Baru Pusat 1
	$centro1[$loop][0]=caridata($mysqli,"select avg(tanggungan) from tb_mahasiswa where set_sementara='C1'");
	$centro1[$loop][1]=caridata($mysqli,"select avg(k_pekerjaan) from tb_mahasiswa where set_sementara='C1'");
	$centro1[$loop][2]=caridata($mysqli,"select avg(k_penghasilan) from tb_mahasiswa where set_sementara='C1'");

	//Centroid Baru Pusat 2
	$centro2[$loop][0]=caridata($mysqli,"select avg(tanggungan) from tb_mahasiswa where set_sementara='C2'");
	$centro2[$loop][1]=caridata($mysqli,"select avg(k_pekerjaan) from tb_mahasiswa where set_sementara='C2'");
	$centro2[$loop][2]=caridata($mysqli,"select avg(k_penghasilan) from tb_mahasiswa where set_sementara='C2'");

	//Centroid Baru Pusat 3
	$centro3[$loop][0]=caridata($mysqli,"select avg(tanggungan) from tb_mahasiswa where set_sementara='C3'");
	$centro3[$loop][1]=caridata($mysqli,"select avg(k_pekerjaan) from tb_mahasiswa where set_sementara='C3'");
	$centro3[$loop][2]=caridata($mysqli,"select avg(k_penghasilan) from tb_mahasiswa where set_sementara='C3'");

	$status='true';
	for ($i=0;$i<$jumlahmahasiswa;$i++) { 
		if($clusterawal[$i]!=$clusterakhir[$i]){
			$status='false';
		}
	}

	if($status=='false'){
		$clusterawal=$clusterakhir;
	}
}

echo "Proses berhasil dilakukan sebanyak $loop kali";

function update_mahasiswa($mysqli,$idmhs,$nilai){

	$stmt=$mysqli->prepare("update tb_mahasiswa set 
		set_sementara=?
		where idmhs=?");
	$stmt->bind_param("ss",
		mysqli_real_escape_string($mysqli,$nilai),
		mysqli_real_escape_string($mysqli,$idmhs));
	$stmt->execute();
}
?>
