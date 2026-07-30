<?php

// ── 1. Baca CSV: kategori donasi (Rendah / Sedang / Tinggi) ─────────────────
$rendah = 0;
$sedang = 0;
$tinggi = 0;

// ── 2. Baca CSV: data aktual per tanggal (untuk grafik aktual vs prediksi) ──
$map_tanggal = [];   // ['YYYY-MM-DD' => total_nominal]

$csv_file = __DIR__ . "/hasil_prediksi.csv";

if (!file_exists($csv_file)) {
    die("File CSV tidak ditemukan");
}

$file = fopen($csv_file, "r");
$header = fgetcsv($file);

$idx_nominal  = array_search('nominal_donasi',  $header);
$idx_tanggal  = array_search('tanggal_donasi',  $header);

// fallback jika header tidak ditemukan (gunakan posisi kolom default)
if ($idx_nominal === false) $idx_nominal = 3;
if ($idx_tanggal === false) $idx_tanggal = 1;

while (($row = fgetcsv($file)) !== FALSE) {
    // Kategori berdasarkan nominal
    $nominal = isset($row[$idx_nominal]) ? (int)$row[$idx_nominal] : 0;
    if ($nominal < 100000)      $rendah++;
    elseif ($nominal <= 300000) $sedang++;
    else                        $tinggi++;

    // Akumulasi per tanggal
    $tgl = isset($row[$idx_tanggal]) ? trim($row[$idx_tanggal]) : '';
    if ($tgl !== '') {
        $map_tanggal[$tgl] = ($map_tanggal[$tgl] ?? 0) + $nominal;
    }
}
fclose($file);

$total = $rendah + $sedang + $tinggi;

// ── 3. Hitung regresi linear untuk prediksi tren ────────────────────────────
ksort($map_tanggal);
$seri_waktu = array_values($map_tanggal);
$seri_label = array_keys($map_tanggal);

// Siapkan label tampilan (d M)
$label_aktual = [];
foreach ($seri_label as $tgl) {
    $label_aktual[] = date('d M', strtotime($tgl));
}

// Regresi linear (least squares) pada data historis
$n = count($seri_waktu);
$x = range(0, $n - 1);

$sum_x  = array_sum($x);
$sum_y  = array_sum($seri_waktu);
$sum_xy = 0;
$sum_x2 = 0;
foreach ($x as $i => $xi) {
    $sum_xy += $xi * $seri_waktu[$i];
    $sum_x2 += $xi * $xi;
}
$denom    = $n * $sum_x2 - $sum_x * $sum_x;
$slope    = ($denom != 0) ? ($n * $sum_xy - $sum_x * $sum_y) / $denom : 0;
$intercept = ($n > 0) ? ($sum_y - $slope * $sum_x) / $n : 0;

// Garis prediksi pada titik historis
$chart_prediksi_hist = [];
foreach ($x as $xi) {
    $chart_prediksi_hist[] = max(0, (int) round($slope * $xi + $intercept));
}

// Prediksi 3 hari ke depan
$label_pred  = [];
$pred_values = [];
$last_tgl    = end($seri_label);
for ($d = 1; $d <= 3; $d++) {
    $ts = strtotime($last_tgl) + $d * 86400;
    $label_pred[]  = date('d M', $ts);
    $pred_values[] = max(0, (int) round($slope * ($n - 1 + $d) + $intercept));
}

// Gabungkan label untuk grafik 2
$all_labels    = array_merge($label_aktual, $label_pred);
// aktual: null pada titik prediksi masa depan
$all_aktual    = array_merge($seri_waktu, array_fill(0, 3, null));
// prediksi: mencakup keseluruhan (historis + 3 hari ke depan)
$all_prediksi  = array_merge($chart_prediksi_hist, $pred_values);

// R² untuk keterangan model
$mean_y  = ($n > 0) ? $sum_y / $n : 0;
$ss_tot  = 0; $ss_res = 0;
foreach ($seri_waktu as $i => $yi) {
    $pi      = $slope * $x[$i] + $intercept;
    $ss_tot += pow($yi - $mean_y, 2);
    $ss_res += pow($yi - $pi, 2);
}
$r2 = ($ss_tot > 0) ? round((1 - $ss_res / $ss_tot) * 100, 1) : 0;

// Encode ke JSON untuk JavaScript
$js_labels_cat  = json_encode(['Rendah', 'Sedang', 'Tinggi']);
$js_data_cat    = json_encode([$rendah, $sedang, $tinggi]);
$js_labels_tren = json_encode($all_labels);
$js_aktual      = json_encode($all_aktual);
$js_prediksi    = json_encode($all_prediksi);
$js_split_idx   = $n; // indeks pertama titik prediksi masa depan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AI Prediksi Donasi</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #e3f2fd;
            padding: 30px 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
        }

        /* TOMBOL KEMBALI */
        .back {
            text-decoration: none;
            background: #4caf50;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            color: #0d47a1;
            margin-bottom: 24px;
            font-size: 22px;
        }

        /* SCORECARD ATAS */
        .scorecard-wrap {
            display: flex;
            gap: 16px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .scorecard {
            flex: 1;
            min-width: 140px;
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .scorecard.rendah { background: linear-gradient(135deg, #ef5350, #c62828); }
        .scorecard.sedang { background: linear-gradient(135deg, #ffa726, #e65100); }
        .scorecard.tinggi { background: linear-gradient(135deg, #26c6da, #0277bd); }

        .scorecard .icon { font-size: 32px; line-height: 1; }
        .scorecard .info { color: white; }
        .scorecard .info .label {
            font-size: 13px; opacity: 0.9; font-weight: 500;
            letter-spacing: .5px; text-transform: uppercase;
        }
        .scorecard .info .angka { font-size: 30px; font-weight: 700; line-height: 1.1; }
        .scorecard .info .persen { font-size: 12px; opacity: 0.8; margin-top: 2px; }

        /* GRAFIK */
        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 8px;
        }

        @media (max-width: 700px) {
            .chart-grid { grid-template-columns: 1fr; }
        }

        .chart-wrap {
            background: #fafafa;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e8e8e8;
        }

        .chart-title {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 14px;
        }

        .chart-subtitle {
            font-size: 12px;
            color: #888;
            margin-bottom: 12px;
        }

        /* Badge tren */
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-left: 8px;
            vertical-align: middle;
        }
        .badge.naik  { background: #e8f5e9; color: #2e7d32; }
        .badge.turun { background: #ffebee; color: #c62828; }
        .badge.stag  { background: #f5f5f5; color: #555; }

        /* Link ke halaman prediksi */
        .goto-prediksi {
            display: inline-block;
            margin-top: 10px;
            font-size: 12px;
            color: #1565c0;
            text-decoration: none;
            font-weight: 500;
        }
        .goto-prediksi:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">

    <a href="about.php" class="back">← Kembali Dashboard</a>
    <h2>Analisis Donasi AI</h2>

    <!-- SCORECARD -->
    <div class="scorecard-wrap">
        <div class="scorecard rendah">
            <div class="icon">📉</div>
            <div class="info">
                <div class="label">Rendah</div>
                <div class="angka"><?= $rendah ?></div>
                <div class="persen"><?= $total > 0 ? round($rendah/$total*100,1) : 0 ?>% dari total</div>
            </div>
        </div>
        <div class="scorecard sedang">
            <div class="icon">📊</div>
            <div class="info">
                <div class="label">Sedang</div>
                <div class="angka"><?= $sedang ?></div>
                <div class="persen"><?= $total > 0 ? round($sedang/$total*100,1) : 0 ?>% dari total</div>
            </div>
        </div>
        <div class="scorecard tinggi">
            <div class="icon">📈</div>
            <div class="info">
                <div class="label">Tinggi</div>
                <div class="angka"><?= $tinggi ?></div>
                <div class="persen"><?= $total > 0 ? round($tinggi/$total*100,1) : 0 ?>% dari total</div>
            </div>
        </div>
    </div>

    <!-- DUA GRAFIK SIDE BY SIDE -->
    <div class="chart-grid">

        <!-- GRAFIK 1: Aktual (Distribusi Kategori) -->
        <div class="chart-wrap">
            <div class="chart-title">📋 Distribusi Aktual Donasi</div>
            <div class="chart-subtitle">Sebaran jumlah donatur berdasarkan nominal aktual dari data CSV</div>
            <canvas id="grafikAktual" height="220"></canvas>
        </div>

        <!-- GRAFIK 2: Prediksi Tren Nominal -->
        <div class="chart-wrap">
            <div class="chart-title">
                🤖 Tren Aktual &amp; Prediksi (Regresi Linear)
                <?php
                    if ($slope > 0)      echo '<span class="badge naik">▲ Naik</span>';
                    elseif ($slope < 0)  echo '<span class="badge turun">▼ Turun</span>';
                    else                 echo '<span class="badge stag">— Stagnan</span>';
                ?>
            </div>
            <div class="chart-subtitle">
                R² = <?= $r2 ?>% &mdash; garis prediksi 3 hari ke depan dihitung dengan model regresi linear
                (<?= $n ?> hari historis).<br>
                <a href="prediksi_ai.php" class="goto-prediksi">→ Input estimasi manual &amp; lihat analisis lengkap</a>
            </div>
            <canvas id="grafikPrediksi" height="220"></canvas>
        </div>

    </div>

</div>

<script>
// ── Grafik 1: Distribusi Aktual (Bar) ──────────────────────────────────────
const ctx1 = document.getElementById('grafikAktual');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?= $js_labels_cat ?>,
        datasets: [{
            label: 'Jumlah Donatur',
            data: <?= $js_data_cat ?>,
            backgroundColor: [
                'rgba(239, 83, 80, 0.8)',
                'rgba(255, 167, 38, 0.8)',
                'rgba(38, 198, 218, 0.8)'
            ],
            borderColor: ['#c62828', '#e65100', '#0277bd'],
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const total = <?= $total ?>;
                        const val   = ctx.parsed.y;
                        const pct   = total > 0 ? (val / total * 100).toFixed(1) : 0;
                        return ` ${val} donatur (${pct}%)`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#666' },
                grid:  { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                ticks: { color: '#555', font: { weight: '600' } },
                grid:  { display: false }
            }
        }
    }
});

// ── Grafik 2: Aktual vs Prediksi (Line) ────────────────────────────────────
const ctx2 = document.getElementById('grafikPrediksi');
const splitIdx = <?= $js_split_idx ?>;
const allLabels = <?= $js_labels_tren ?>;

new Chart(ctx2, {
    type: 'line',
    data: {
        labels: allLabels,
        datasets: [
            {
                label: 'Aktual',
                data: <?= $js_aktual ?>,
                borderColor: '#1565c0',
                backgroundColor: 'rgba(21, 101, 192, 0.08)',
                borderWidth: 2.5,
                pointRadius: 5,
                pointBackgroundColor: '#1565c0',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                fill: true,
                tension: 0.3,
                spanGaps: false
            },
            {
                label: 'Prediksi (Regresi)',
                data: <?= $js_prediksi ?>,
                borderColor: '#e65100',
                backgroundColor: 'rgba(230, 81, 0, 0.06)',
                borderWidth: 2,
                borderDash: [6, 4],
                pointRadius: function(ctx) {
                    // Titik lebih besar pada area prediksi ke depan
                    return ctx.dataIndex >= splitIdx ? 7 : 4;
                },
                pointBackgroundColor: function(ctx) {
                    return ctx.dataIndex >= splitIdx ? '#e65100' : 'rgba(230,81,0,0.4)';
                },
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                fill: false,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                labels: { font: { size: 12 }, color: '#444' }
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const val = ctx.parsed.y;
                        if (val === null || val === undefined) return null;
                        return ` ${ctx.dataset.label}: Rp ${val.toLocaleString('id-ID')}`;
                    },
                    title: function(items) {
                        const idx = items[0]?.dataIndex ?? 0;
                        const suffix = idx >= splitIdx ? ' 🔮 Prediksi' : '';
                        return items[0]?.label + suffix;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    color: '#666',
                    callback: function(val) {
                        if (val >= 1000000) return 'Rp ' + (val/1000000).toFixed(1) + 'jt';
                        if (val >= 1000)    return 'Rp ' + (val/1000).toFixed(0) + 'rb';
                        return 'Rp ' + val;
                    }
                },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                ticks: { color: '#666', maxRotation: 45 },
                grid:  { display: false }
            }
        }
    }
});
</script>
</body>
</html>