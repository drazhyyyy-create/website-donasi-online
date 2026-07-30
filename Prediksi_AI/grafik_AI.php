<?php

$rendah = 0;
$sedang = 0;
$tinggi = 0;

$csv_file = __DIR__ . "/hasil_prediksi.csv";

if (!file_exists($csv_file)) {
    die("File CSV tidak ditemukan");
}

$file = fopen($csv_file, "r");

// Skip header
fgetcsv($file);

while(($row = fgetcsv($file)) !== FALSE){

    $nominal = (int)$row[3]; // kolom nominal_donasi

    if($nominal < 100000){
        $rendah++;
    }
    elseif($nominal <= 300000){
        $sedang++;
    }
    else{
        $tinggi++;
    }
}

fclose($file);

echo "Rendah: $rendah <br>";
echo "Sedang: $sedang <br>";
echo "Tinggi: $tinggi <br>";

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>AI Prediksi Donasi</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body{
            font-family:Segoe UI;
            background:#e3f2fd;
            padding:30px;
        }

        .container{
            max-width:1000px;
            margin:auto;
            background:white;
            padding:25px;
            border-radius:10px;
            box-shadow:0 2px 10px rgba(0,0,0,.1);
        }

        h2{
            text-align:center;
            color:#0d47a1;
        }

        .card{
            display:flex;
            justify-content:space-around;
            margin-bottom:30px;
        }

        .box{
            background:#2196f3;
            color:white;
            padding:20px;
            border-radius:10px;
            width:200px;
            text-align:center;
        }

        .back{
            text-decoration:none;
            background:#4caf50;
            color:white;
            padding:10px 15px;
            border-radius:5px;
        }
    </style>
</head>
<body>

<div class="container">

    <a href="about.php" class="back">
        Kembali Dashboard
    </a>

    <h2>Analisis Donasi AI</h2>

    <div class="card">

        <div class="box">
            <h3>Rendah</h3>
            <h2><?= $rendah ?></h2>
        </div>

        <div class="box">
            <h3>Sedang</h3>
            <h2><?= $sedang ?></h2>
        </div>

        <div class="box">
            <h3>Tinggi</h3>
            <h2><?= $tinggi ?></h2>
        </div>

    </div>

    <canvas id="grafikDonasi"></canvas>

</div>

<script>

const ctx = document.getElementById('grafikDonasi');

new Chart(ctx,{
    type:'bar',
    data:{
        labels:['Rendah','Sedang','Tinggi'],
        datasets:[{
            label:'Jumlah Donasi',
            data:[
                <?= $rendah ?>,
                <?= $sedang ?>,
                <?= $tinggi ?>
            ]
        }]
    }
});

</script>

</body>
</html>
```
