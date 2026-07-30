<?php
$conn = mysqli_connect("localhost", "root", "", "donasi");

$hasil       = "";
$data_filter = [];

$csv_file = 'hasil_prediksi.csv';

$kategori_jenis_map = [];
$semua_header       = [];

// ── Data seri waktu dari CSV ──────────────────────────────────────────────
$seri_waktu     = [];
$all_rows_cache = []; // semua row untuk training Regresi Linear

if (($handle = fopen($csv_file, 'r')) !== false) {
    $semua_header = fgetcsv($handle);

    $idx_kategori = array_search('kategori_donasi',   $semua_header);
    $idx_jenis    = array_search('alasan_donasi',     $semua_header);
    $idx_nominal  = array_search('nominal_donasi',    $semua_header);
    $idx_tanggal  = array_search('tanggal_donasi',    $semua_header);
    $idx_metode   = array_search('metode_pembayaran', $semua_header);
    $idx_rutin    = array_search('donasi_rutin',      $semua_header);
    $idx_sumber   = array_search('sumber_informasi',  $semua_header);
    $idx_pernah   = array_search('pernah_donasi',     $semua_header);

    $map_tanggal = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[$idx_kategori]) || !isset($row[$idx_jenis])) continue;

        $kat   = trim($row[$idx_kategori]);
        $jenis = trim($row[$idx_jenis]);
        if ($kat === '' || $jenis === '') continue;

        if (!isset($kategori_jenis_map[$kat])) $kategori_jenis_map[$kat] = [];
        if (!in_array($jenis, $kategori_jenis_map[$kat]))
            $kategori_jenis_map[$kat][] = $jenis;

        if (isset($row[$idx_tanggal]) && isset($row[$idx_nominal])) {
            $tgl = trim($row[$idx_tanggal]);
            $nom = (int) trim($row[$idx_nominal]);
            if ($tgl !== '') {
                if (!isset($map_tanggal[$tgl]))
                    $map_tanggal[$tgl] = ['total' => 0, 'count' => 0];
                $map_tanggal[$tgl]['total'] += $nom;
                $map_tanggal[$tgl]['count']++;
            }
        }

        // Cache untuk training Regresi Linear
        $all_rows_cache[] = [
            'nominal'  => (int) trim($row[$idx_nominal]  ?? 0),
            'kategori' => $kat,
            'jenis'    => $jenis,
            'metode'   => trim($row[$idx_metode]  ?? ''),
            'rutin'    => strtolower(trim($row[$idx_rutin]  ?? '')),
            'sumber'   => trim($row[$idx_sumber]  ?? ''),
            'pernah'   => strtolower(trim($row[$idx_pernah] ?? '')),
            'tanggal'  => trim($row[$idx_tanggal] ?? ''),
        ];
    }
    fclose($handle);
}

ksort($map_tanggal);
foreach ($map_tanggal as $tgl => $info) {
    $seri_waktu[] = [
        'tanggal' => $tgl,
        'total'   => $info['total'],
        'count'   => $info['count'],
    ];
}

ksort($kategori_jenis_map);
foreach ($kategori_jenis_map as &$jl) sort($jl);
unset($jl);

$semua_kategori = array_keys($kategori_jenis_map);

$label_kolom = [
    'id_donatur'        => 'ID Donatur',
    'tanggal_donasi'    => 'Tanggal',
    'kategori_donasi'   => 'Kategori',
    'nominal_donasi'    => 'Nominal (Rp)',
    'metode_pembayaran' => 'Metode Bayar',
    'donasi_rutin'      => 'Rutin',
    'sumber_informasi'  => 'Sumber Info',
    'alasan_donasi'     => 'Jenis Donasi',
    'pernah_donasi'     => 'Pernah Donasi',
];

// ════════════════════════════════════════════════════════════════════════════
//  REGRESI LINEAR BERGANDA (Multiple Linear Regression)
//  Target  : nominal_donasi (kontinu)
//  Fitur   : one-hot encoding dari kategori, jenis, metode, sumber
//            + biner untuk donasi_rutin & pernah_donasi
//  Metode  : Normal Equation  theta = (XᵀX + λI)⁻¹ Xᵀy   (ridge kecil utk stabilitas)
//  Evaluasi: data dibagi Train 80% / Uji 20%, dihitung MSE pada
//            masing-masing subset agar terlihat apakah model overfit/underfit.
// ════════════════════════════════════════════════════════════════════════════

/** Membentuk kamus (vocabulary) nilai unik tiap kolom kategorik */
function lr_build_vocab(array $rows): array
{
    $fields = ['kategori', 'jenis', 'metode', 'sumber'];
    $vocab  = [];
    foreach ($fields as $f) {
        $vals = [];
        foreach ($rows as $r) {
            $v = $r[$f] ?? '';
            if ($v !== '' && !in_array($v, $vals)) $vals[] = $v;
        }
        sort($vals);
        $vocab[$f] = $vals;
    }
    return $vocab;
}

/** Nama-nama fitur hasil encoding (dummy encoding: kategori pertama jadi basis/referensi) */
function lr_build_feature_names(array $vocab): array
{
    $names = ['bias'];
    foreach ($vocab as $f => $vals) {
        for ($i = 1; $i < count($vals); $i++) $names[] = "$f=" . $vals[$i];
    }
    $names[] = 'donasi_rutin=ya';
    $names[] = 'pernah_donasi=ya';
    return $names;
}

/** Mengubah satu baris data menjadi vektor fitur numerik */
function lr_encode_row(array $row, array $vocab, array $feature_names): array
{
    $vec = array_fill(0, count($feature_names), 0.0);
    $vec[0] = 1.0; // bias/intercept
    $idx = 1;
    foreach ($vocab as $f => $vals) {
        for ($i = 1; $i < count($vals); $i++) {
            if (($row[$f] ?? '') === $vals[$i]) $vec[$idx] = 1.0;
            $idx++;
        }
    }
    $vec[$idx++] = (($row['rutin']  ?? '') === 'ya') ? 1.0 : 0.0;
    $vec[$idx++] = (($row['pernah'] ?? '') === 'ya') ? 1.0 : 0.0;
    return $vec;
}

/** Membagi dataset menjadi data latih (train) & data uji (test) secara KRONOLOGIS
 *  berdasarkan tanggal (bukan acak): data dengan tanggal LEBIH LAMA masuk ke
 *  Data Latih, dan data dengan tanggal PALING BARU masuk ke Data Uji.
 *  Ini pendekatan standar untuk time-series, karena model dievaluasi seolah
 *  benar-benar memprediksi "masa depan" yang belum pernah dilihat. */
function lr_split_dataset(array $X, array $y, array $ctx, float $test_ratio): array
{
    $n   = count($X);
    $idx = range(0, $n - 1);

    // Urutkan indeks berdasarkan tanggal (ascending / dari yang paling lama).
    // Tie-breaker memakai indeks asli agar urutan tetap konsisten (stable sort).
    usort($idx, function ($a, $b) use ($ctx) {
        $cmp = strcmp($ctx[$a]['tanggal'], $ctx[$b]['tanggal']);
        return $cmp !== 0 ? $cmp : ($a <=> $b);
    });

    $n_test    = (int) round($n * $test_ratio);
    $n_train   = $n - $n_test;
    $train_idx = array_slice($idx, 0, $n_train);   // bagian tanggal lebih lama
    $test_idx  = array_slice($idx, $n_train);      // bagian tanggal paling baru

    $Xtr = []; $ytr = [];
    foreach ($train_idx as $i) { $Xtr[] = $X[$i]; $ytr[] = $y[$i]; }
    $Xte = []; $yte = [];
    foreach ($test_idx as $i)  { $Xte[] = $X[$i]; $yte[] = $y[$i]; }

    return [$Xtr, $ytr, $Xte, $yte, $train_idx, $test_idx];
}

function lr_mat_transpose(array $M): array
{
    $rows = count($M); $cols = count($M[0]);
    $T = [];
    for ($j = 0; $j < $cols; $j++) {
        $T[$j] = [];
        for ($i = 0; $i < $rows; $i++) $T[$j][$i] = $M[$i][$j];
    }
    return $T;
}

function lr_mat_mul(array $A, array $B): array
{
    $ra = count($A); $ca = count($A[0]); $cb = count($B[0]);
    $C = array_fill(0, $ra, array_fill(0, $cb, 0.0));
    for ($i = 0; $i < $ra; $i++) {
        for ($k = 0; $k < $ca; $k++) {
            if ($A[$i][$k] == 0.0) continue;
            for ($j = 0; $j < $cb; $j++) $C[$i][$j] += $A[$i][$k] * $B[$k][$j];
        }
    }
    return $C;
}

function lr_mat_vec_mul(array $M, array $v): array
{
    $out = [];
    foreach ($M as $row) {
        $s = 0.0;
        foreach ($row as $k => $val) $s += $val * $v[$k];
        $out[] = $s;
    }
    return $out;
}

/** Invers matriks memakai eliminasi Gauss-Jordan (dengan partial pivoting) */
function lr_mat_inverse(array $M): array
{
    $n = count($M);
    $A = $M;
    $I = [];
    for ($i = 0; $i < $n; $i++) {
        $I[$i] = array_fill(0, $n, 0.0);
        $I[$i][$i] = 1.0;
    }
    for ($col = 0; $col < $n; $col++) {
        $pivot  = $col;
        $maxVal = abs($A[$col][$col]);
        for ($r = $col + 1; $r < $n; $r++) {
            if (abs($A[$r][$col]) > $maxVal) { $maxVal = abs($A[$r][$col]); $pivot = $r; }
        }
        if ($pivot != $col) {
            [$A[$col], $A[$pivot]] = [$A[$pivot], $A[$col]];
            [$I[$col], $I[$pivot]] = [$I[$pivot], $I[$col]];
        }
        $d = $A[$col][$col];
        if (abs($d) < 1e-12) $d = 1e-12;
        for ($j = 0; $j < $n; $j++) { $A[$col][$j] /= $d; $I[$col][$j] /= $d; }
        for ($r = 0; $r < $n; $r++) {
            if ($r === $col) continue;
            $factor = $A[$r][$col];
            if ($factor == 0.0) continue;
            for ($j = 0; $j < $n; $j++) {
                $A[$r][$j] -= $factor * $A[$col][$j];
                $I[$r][$j] -= $factor * $I[$col][$j];
            }
        }
    }
    return $I;
}

/** Melatih model Regresi Linear Berganda via Normal Equation + ridge kecil */
function lr_train(array $X, array $y, float $lambda = 1.0): array
{
    if (empty($X)) return [];
    $Xt  = lr_mat_transpose($X);
    $XtX = lr_mat_mul($Xt, $X);
    $n   = count($XtX);
    for ($i = 1; $i < $n; $i++) $XtX[$i][$i] += $lambda; // bias (i=0) tidak diregularisasi
    $XtXinv   = lr_mat_inverse($XtX);
    $Y        = array_map(fn($v) => [$v], $y);
    $Xty      = lr_mat_mul($Xt, $Y);
    $Xty_flat = array_map(fn($r) => $r[0], $Xty);
    return lr_mat_vec_mul($XtXinv, $Xty_flat);
}

function lr_predict(array $theta, array $x): float
{
    if (empty($theta)) return 0.0;
    $s = 0.0;
    foreach ($x as $i => $v) $s += ($theta[$i] ?? 0.0) * $v;
    return $s;
}

/** Mean Squared Error */
function lr_mse(array $y_true, array $y_pred): float
{
    $n = count($y_true);
    if ($n === 0) return 0.0;
    $s = 0.0;
    for ($i = 0; $i < $n; $i++) $s += ($y_true[$i] - $y_pred[$i]) ** 2;
    return $s / $n;
}

/** Symmetric Mean Absolute Percentage Error (%) — dibatasi rentang 0-200%,
 *  dipakai untuk mengubah hasil evaluasi dari satuan Rupiah (nominal) menjadi
 *  persentase kesalahan/akurasi. Lebih stabil daripada MAPE biasa karena tidak
 *  meledak ketika nominal aktual kecil (pembagi memakai rata-rata |aktual|+|prediksi|,
 *  bukan |aktual| saja). */
function lr_smape(array $y_true, array $y_pred): float
{
    $n = count($y_true);
    if ($n === 0) return 0.0;
    $s = 0.0; $cnt = 0;
    for ($i = 0; $i < $n; $i++) {
        $denom = (abs($y_true[$i]) + abs($y_pred[$i])) / 2;
        if ($denom == 0.0) continue; // hindari pembagian nol (aktual & prediksi sama-sama 0)
        $s += abs($y_true[$i] - $y_pred[$i]) / $denom;
        $cnt++;
    }
    return $cnt > 0 ? ($s / $cnt) * 100 : 0.0; // 0% = sempurna, 200% = maksimum meleset
}

// ── Bangun & latih model dari seluruh data CSV ────────────────────────────
$lr_vocab         = lr_build_vocab($all_rows_cache);
$lr_feature_names = lr_build_feature_names($lr_vocab);

$lr_X = []; $lr_y = []; $lr_ctx = [];
foreach ($all_rows_cache as $r) {
    if ($r['nominal'] <= 0) continue;
    $lr_X[]   = lr_encode_row($r, $lr_vocab, $lr_feature_names);
    $lr_y[]   = (float) $r['nominal'];
    $lr_ctx[] = $r; // simpan konteks asli (kategori, jenis, metode, dst) untuk ditampilkan
}

$lr_eval = [
    'n_total' => count($lr_X), 'n_train' => 0, 'n_test' => 0,
    'mse_train' => 0, 'mse_test' => 0, 'rmse_train' => 0, 'rmse_test' => 0,
    'train_tgl_awal' => '-', 'train_tgl_akhir' => '-',
    'test_tgl_awal' => '-', 'test_tgl_akhir' => '-',
];
$lr_theta      = [];
$lr_q33        = 0;
$lr_q66        = 0;
$lr_top_fit    = []; // fitur paling berpengaruh (untuk interpretasi)
$lr_detail_all = []; // isi data latih & uji (aktual vs prediksi) untuk ditampilkan

if (count($lr_X) >= 10) {
    [$lr_Xtr, $lr_ytr, $lr_Xte, $lr_yte, $lr_train_idx, $lr_test_idx] =
        lr_split_dataset($lr_X, $lr_y, $lr_ctx, 0.2);

    // Rentang tanggal masing-masing set (untuk ditampilkan di halaman)
    $tgl_train_all = array_map(fn($i) => $lr_ctx[$i]['tanggal'], $lr_train_idx);
    $tgl_test_all  = array_map(fn($i) => $lr_ctx[$i]['tanggal'], $lr_test_idx);
    sort($tgl_train_all);
    sort($tgl_test_all);
    $lr_eval['train_tgl_awal']  = $tgl_train_all[0]        ?? '-';
    $lr_eval['train_tgl_akhir'] = end($tgl_train_all)      ?: '-';
    $lr_eval['test_tgl_awal']   = $tgl_test_all[0]         ?? '-';
    $lr_eval['test_tgl_akhir']  = end($tgl_test_all)       ?: '-';

    // Model final dilatih dari data TRAIN saja (agar evaluasi di data UJI valid/jujur)
    $lr_theta = lr_train($lr_Xtr, $lr_ytr, 1.0);

    $ytr_pred = array_map(fn($x) => lr_predict($lr_theta, $x), $lr_Xtr);
    $yte_pred = array_map(fn($x) => lr_predict($lr_theta, $x), $lr_Xte);

    $lr_eval['n_train']      = count($lr_Xtr);
    $lr_eval['n_test']       = count($lr_Xte);
    $lr_eval['mse_train']    = lr_mse($lr_ytr, $ytr_pred);
    $lr_eval['mse_test']     = lr_mse($lr_yte, $yte_pred);
    $lr_eval['rmse_train']   = sqrt($lr_eval['mse_train']);
    $lr_eval['rmse_test']    = sqrt($lr_eval['mse_test']);
    // Total kuadrat selisih (Σ(aktual - prediksi)²) per set — komponen pembilang dari MSE = Total ÷ n
    $lr_eval['sumsq_train']  = $lr_eval['mse_train'] * $lr_eval['n_train'];
    $lr_eval['sumsq_test']   = $lr_eval['mse_test']  * $lr_eval['n_test'];

    // Kuantil P33/P66 dari seluruh nominal (untuk label Rendah/Sedang/Tinggi)
    $sorted_y = $lr_y;
    sort($sorted_y);
    $ny     = count($sorted_y);
    $lr_q33 = $sorted_y[(int) ($ny * 0.33)];
    $lr_q66 = $sorted_y[(int) ($ny * 0.66)];

    // Fitur paling berpengaruh (|koefisien| terbesar, tanpa bias)
    $coef_pairs = [];
    foreach ($lr_feature_names as $i => $name) {
        if ($i === 0) continue; // skip bias
        $coef_pairs[] = ['name' => $name, 'coef' => $lr_theta[$i] ?? 0.0];
    }
    usort($coef_pairs, fn($a, $b) => abs($b['coef']) <=> abs($a['coef']));
    $lr_top_fit = array_slice($coef_pairs, 0, 5);

    // Bangun daftar isi Data Latih & Data Uji (aktual vs prediksi + selisih)
    foreach ($lr_train_idx as $pos => $origIdx) {
        $akt = $lr_ytr[$pos]; $prd = $ytr_pred[$pos];
        $denom = (abs($akt) + abs($prd)) / 2;
        $lr_detail_all[] = [
            'set'        => 'Latih',
            'ctx'        => $lr_ctx[$origIdx],
            'aktual'     => $akt,
            'prediksi'   => $prd,
            'selisih_rp' => abs($akt - $prd), // kontribusi baris ini ke rata-rata (MSE/RMSE) Data Latih
            'error_pct'  => $denom != 0 ? abs($akt - $prd) / $denom * 100 : 0, // SMAPE per-baris (0-200%)
        ];
    }
    foreach ($lr_test_idx as $pos => $origIdx) {
        $akt = $lr_yte[$pos]; $prd = $yte_pred[$pos];
        $denom = (abs($akt) + abs($prd)) / 2;
        $lr_detail_all[] = [
            'set'        => 'Uji',
            'ctx'        => $lr_ctx[$origIdx],
            'aktual'     => $akt,
            'prediksi'   => $prd,
            'selisih_rp' => abs($akt - $prd), // kontribusi baris ini ke rata-rata (MSE/RMSE) Data Uji
            'error_pct'  => $denom != 0 ? abs($akt - $prd) / $denom * 100 : 0, // SMAPE per-baris (0-200%)
        ];
    }
}

/** Prediksi nominal donasi untuk satu konteks input (dipakai form 1 & form 2) */
function lr_predict_context(array $theta, array $vocab, array $feature_names, array $input): float
{
    $x = lr_encode_row($input, $vocab, $feature_names);
    return lr_predict($theta, $x);
}

function lr_klasifikasi(float $nilai, float $q33, float $q66): string
{
    if ($nilai <= $q33)     return 'low';
    elseif ($nilai <= $q66) return 'mid';
    else                    return 'high';
}

/** Moving average (rata-rata bergerak) window ganjil, dipusatkan di tiap titik.
 *  Dipakai untuk meredam fluktuasi harian sebelum menghitung tren linear,
 *  supaya kenaikan/penurunan sesaat tidak membuat garis tren jadi "tidak beraturan"
 *  padahal arah tren jangka menengahnya sebenarnya cukup jelas. */
function lr_moving_average(array $y, int $window = 3): array
{
    $n = count($y);
    if ($n === 0) return [];
    $half = intdiv($window, 2);
    $out  = [];
    for ($i = 0; $i < $n; $i++) {
        $lo    = max(0, $i - $half);
        $hi    = min($n - 1, $i + $half);
        $slice = array_slice($y, $lo, $hi - $lo + 1);
        $out[] = array_sum($slice) / count($slice);
    }
    return $out;
}

// ════════════════════════════════════════════════════════════════════════════
//  Input prediksi waktu
// ════════════════════════════════════════════════════════════════════════════
$input_tanggal    = $_POST['tanggal_awal']     ?? '';
$input_tgl_target = $_POST['tanggal_prediksi'] ?? ''; // tanggal bebas yang ingin diprediksi
$input_nominal    = (int)($_POST['nominal_input'] ?? 0);
$input_kat     = $_POST['filter_kategori'] ?? '';
$input_metode  = $_POST['filter_metode']   ?? '';
$input_rutin   = $_POST['filter_rutin']    ?? '';

$chart_labels   = [];
$chart_aktual   = [];
$chart_prediksi = [];
$analisis_teks  = '';
$prediksi_nilai = 0;
$trend_type     = '';
$lr_result      = null;

if (isset($_POST['prediksi_waktu']) && $input_tanggal !== '' && $input_tgl_target !== '' && $input_nominal > 0) {

    // ── 7 hari terakhir data aktual ──────────────────────────────────────
    $n     = count($seri_waktu);
    $slice = array_slice($seri_waktu, max(0, $n - 7));

    // Fallback jika data kosong: buat 7 titik mendekati input nominal
    if (empty($slice)) {
        srand(crc32($input_tanggal . $input_nominal)); // seed deterministik
        $base = $input_nominal;
        for ($d = 6; $d >= 0; $d--) {
            $ts     = strtotime($input_tanggal) - $d * 86400;
            $factor = 0.80 + (rand(0, 40) / 100);   // variasi ±20%
            $slice[] = [
                'tanggal' => date('Y-m-d', $ts),
                'total'   => (int)($base * $factor),
                'count'   => rand(3, 15),
            ];
        }
    }

    foreach ($slice as $item) {
        $chart_labels[] = date('d M', strtotime($item['tanggal']));
        $chart_aktual[] = (int)$item['total'];
    }

    // ── Regresi linear sederhana (tren waktu, 1 variabel: indeks hari) ────
    // Fit dilakukan pada data yang sudah dihaluskan (moving average 3 hari) agar
    // fluktuasi naik-turun harian tidak membuat tren terlihat "tidak beraturan"
    // padahal itu murni noise jangka pendek. Titik aktual di grafik tetap memakai
    // data mentah ($chart_aktual) — hanya perhitungan tren yang memakai versi halus.
    $y_raw = $chart_aktual;
    $y     = lr_moving_average($y_raw, 3);
    $x     = range(0, count($y) - 1);
    $nx    = count($x);

    $sum_x  = array_sum($x);
    $sum_y  = array_sum($y);
    $sum_xy = 0; $sum_x2 = 0;
    foreach ($x as $i => $xi) {
        $sum_xy += $xi * $y[$i];
        $sum_x2 += $xi ** 2;
    }
    $denom     = $nx * $sum_x2 - $sum_x ** 2;
    $slope     = ($denom != 0) ? ($nx * $sum_xy - $sum_x * $sum_y) / $denom : 0;
    $intercept = ($sum_y - $slope * $sum_x) / $nx;

    // Selisih kuadrat rata-rata (MSE) tren waktu, dipakai untuk menilai kestabilan tren
    $mse_tren = 0;
    foreach ($y as $i => $yi) {
        $pred_i    = $slope * $x[$i] + $intercept;
        $mse_tren += ($yi - $pred_i) ** 2;
    }
    $mse_tren = $nx > 0 ? $mse_tren / $nx : 0;
    $rmse_tren = sqrt($mse_tren);
    $mean_y_tren = $nx > 0 ? $sum_y / $nx : 0;
    // Rasio RMSE tren terhadap rata-rata nominal — dipakai hanya untuk memilih label tren
    $rmse_rel_tren = $mean_y_tren > 0 ? ($rmse_tren / $mean_y_tren) : 1;

    // ── Garis prediksi untuk titik-titik historis ────────────────────────
    // Blend: 65% aktual (mengikuti data asli) + 35% estimasi Regresi Linear Berganda
    foreach ($slice as $i => $item) {
        $ctx = [
            'kategori' => $input_kat,
            'jenis'    => '',
            'metode'   => $input_metode,
            'rutin'    => $input_rutin,
            'sumber'   => '',
            'pernah'   => '',
        ];
        $lr_pt = !empty($lr_theta)
            ? lr_predict_context($lr_theta, $lr_vocab, $lr_feature_names, $ctx)
            : 0.0;

        $blended = ($lr_pt > 0)
            ? (int)round($item['total'] * 0.65 + $lr_pt * 0.35)
            : (int)$item['total'];

        $chart_prediksi[] = $blended;
    }

    // ── Prediksi ke depan (tanggal target dipilih bebas oleh pengguna) ─────
    $selisih_hari = (int) round((strtotime($input_tgl_target) - strtotime($input_tanggal)) / 86400);
    if ($selisih_hari < 1) $selisih_hari = 1; // tanggal target minimal 1 hari setelah tanggal referensi
    $step   = $selisih_hari;
    $next_x = $nx - 1 + $step;

    $pred_regresi_waktu = (int)round($slope * $next_x + $intercept);

    $lr_pred_fwd = !empty($lr_theta)
        ? lr_predict_context($lr_theta, $lr_vocab, $lr_feature_names, [
            'kategori' => $input_kat, 'jenis' => '', 'metode' => $input_metode,
            'rutin' => $input_rutin, 'sumber' => '', 'pernah' => '',
        ])
        : 0.0;
    $lr_cls  = lr_klasifikasi($lr_pred_fwd > 0 ? $lr_pred_fwd : $input_nominal, $lr_q33, $lr_q66);
    $lr_result = ['class' => $lr_cls, 'mean' => $lr_pred_fwd];

    // Ensemble: 40% regresi tren waktu + 35% regresi linear berganda + 25% input user
    $prediksi_nilai = (int)round(
        $pred_regresi_waktu * 0.40 +
        $lr_pred_fwd         * 0.35 +
        $input_nominal       * 0.25
    );

    $tgl_pred   = $input_tgl_target;
    $label_pred = 'Target (' . date('d M Y', strtotime($tgl_pred)) . ')';

    $chart_labels[]   = $label_pred;
    $chart_aktual[]   = null;
    $chart_prediksi[] = $prediksi_nilai;

    // ── Analisis tren (dinilai dari RMSE relatif tren waktu terhadap rata-rata nominal) ──
    $slope_fmt = number_format(abs($slope), 0, ',', '.');
    $rmse_rel_pct = round($rmse_rel_tren * 100, 1);

    if ($rmse_rel_tren <= 0.15)      { $trend_type = 'Linear Kuat';
        $fit_desc = "Pola <strong>linear kuat</strong> (simpangan tren ≈{$rmse_rel_pct}% dari rata-rata nominal, dihitung dari data yang sudah dihaluskan/rata-rata bergerak 3 hari) — prediksi regresi tren waktu sangat andal."; }
    elseif ($rmse_rel_tren <= 0.35)  { $trend_type = 'Linear Moderat';
        $fit_desc = "<strong>Linear moderat</strong> (simpangan tren ≈{$rmse_rel_pct}%, dari data yang sudah dihaluskan) — tren jelas dengan variasi wajar."; }
    elseif ($rmse_rel_tren <= 0.60)  { $trend_type = 'Non-Linear / Fluktuatif';
        $fit_desc = "Data <strong>fluktuatif</strong> (simpangan tren ≈{$rmse_rel_pct}%, dari data yang sudah dihaluskan) — regresi berganda membantu menangkap pola dari fitur kategorik."; }
    else                             { $trend_type = 'Sangat Tidak Beraturan';
        $fit_desc = "Data <strong>sangat tidak beraturan</strong> bahkan setelah dihaluskan (simpangan tren ≈{$rmse_rel_pct}%) — fluktuasi hariannya memang besar, hasil regresi sebaiknya dipakai sebagai estimasi kasar."; }

    if ($slope > 0)     $dir_desc = "Tren <strong>meningkat</strong> rata-rata Rp {$slope_fmt}/hari.";
    elseif ($slope < 0) $dir_desc = "Tren <strong>menurun</strong> rata-rata Rp {$slope_fmt}/hari.";
    else                $dir_desc = "Tren <strong>stagnan</strong> tanpa perubahan signifikan.";

    $cls_label   = ['low' => 'Rendah 🔵', 'mid' => 'Sedang 🟡', 'high' => 'Tinggi 🟢'];
    $lr_cls_disp = $cls_label[$lr_result['class']] ?? $lr_result['class'];

    $lbl_periode = date('d F Y', strtotime($tgl_pred)) . " ({$step} hari dari tanggal referensi)";
    $analisis_teks = "
        <p>📊 {$fit_desc}</p>
        <p>📈 {$dir_desc}</p>
        <p>📐 <strong>Regresi Linear Berganda</strong> mengestimasi nominal konteks ini sebagai
           <strong class='lr-class-badge'>{$lr_cls_disp}</strong>
           (estimasi = Rp " . number_format((int)$lr_pred_fwd, 0, ',', '.') . ")</p>
        <p>🎯 Prediksi nominal untuk <strong>{$lbl_periode}</strong>:
           <span class='pred-nominal'>Rp " . number_format($prediksi_nilai, 0, ',', '.') . "</span>
        </p>
        <p class='formula-note'>Ensemble: 40% regresi tren waktu + 35% regresi linear berganda + 25% estimasi Anda
           (Rp " . number_format($input_nominal, 0, ',', '.') . ")</p>
        <p class='formula-note'>Evaluasi model (data uji, 20% dari " . $lr_eval['n_total'] . " baris):
           rata-rata selisih ≈ Rp " . number_format($lr_eval['rmse_test'], 0, ',', '.') . "</p>
    ";
}

// ── Prediksi kategori (form 1) — Regresi Linear Berganda ─────────────────────
if (isset($_POST['prediksi'])) {
    $kategori = $_POST['kategori'];
    $jenis    = $_POST['jenis_donasi'];

    $pred_nominal_kat = !empty($lr_theta)
        ? lr_predict_context($lr_theta, $lr_vocab, $lr_feature_names, [
            'kategori' => $kategori, 'jenis' => $jenis,
            'metode' => '', 'rutin' => '', 'sumber' => '', 'pernah' => '',
        ])
        : 0.0;
    $kelas_pred = lr_klasifikasi($pred_nominal_kat, $lr_q33, $lr_q66);

    $cls_map = ['low' => 'Potensi Donasi Rendah', 'mid' => 'Potensi Donasi Sedang', 'high' => 'Potensi Donasi Tinggi'];
    $hasil   = $cls_map[$kelas_pred] ?? 'Potensi Donasi Sedang';
    $hasil  .= " &nbsp;|&nbsp; Estimasi Regresi Linear: <strong>Rp " .
               number_format((int)$pred_nominal_kat, 0, ',', '.') . "</strong>";

    if (($handle = fopen($csv_file, 'r')) !== false) {
        $header = fgetcsv($handle);
        $idx_k  = array_search('kategori_donasi', $header);
        $idx_j  = array_search('alasan_donasi',   $header);
        while (($row = fgetcsv($handle)) !== false) {
            if (trim($row[$idx_k]) === $kategori && trim($row[$idx_j]) === $jenis)
                $data_filter[] = array_combine($header, $row);
        }
        fclose($handle);
    }
}

// Daftar metode unik untuk dropdown filter
$semua_metode = [];
foreach ($all_rows_cache as $r) {
    if ($r['metode'] !== '' && !in_array($r['metode'], $semua_metode))
        $semua_metode[] = $r['metode'];
}
sort($semua_metode);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prediksi AI Donasi</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-dark:   #0d2b6e;
            --blue-mid:    #1a4fc4;
            --blue-light:  #3b82f6;
            --green:       #10b981;
            --amber:       #f59e0b;
            --red:         #ef4444;
            --surface:     #f0f6ff;
            --card:        #ffffff;
            --border:      #dde8f8;
            --text-main:   #0f1f45;
            --text-sub:    #4b5e8a;
            --text-muted:  #8898bb;
            --pred-color:  #f59e0b;
            --aktual-color:#10b981;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text-main);
            padding: 32px 16px 60px;
            min-height: 100vh;
        }

        .page-wrap { max-width: 980px; margin: auto; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; background: var(--green); color: #fff;
            padding: 9px 18px; border-radius: 8px; font-size: 14px; font-weight: 600;
            margin-bottom: 22px; transition: filter .2s;
        }
        .back-btn:hover { filter: brightness(1.1); }

        .page-title {
            text-align: center; font-size: 26px; font-weight: 800;
            color: var(--blue-dark); letter-spacing: -.5px; margin-bottom: 6px;
        }
        .page-sub {
            text-align: center; font-size: 14px;
            color: var(--text-sub); margin-bottom: 32px;
        }

        .lr-pill {
            display: inline-flex; align-items: center; gap: 5px;
            background: linear-gradient(135deg, #1a4fc4, #0ea5e9);
            color: #fff; font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px;
            vertical-align: middle; margin-left: 8px;
        }

        .card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 14px; padding: 28px 28px 24px; margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(13,43,110,.06);
        }

        .card-title {
            font-size: 16px; font-weight: 700; color: var(--blue-dark);
            margin-bottom: 18px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .card-title .icon {
            width: 30px; height: 30px; background: var(--blue-mid); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; color: #fff; flex-shrink: 0;
        }

        .form-grid  { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .form-grid3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
        .field      { display: flex; flex-direction: column; gap: 6px; }

        label.field-label {
            font-size: 13px; font-weight: 600; color: var(--text-sub);
            text-transform: uppercase; letter-spacing: .4px;
        }

        input[type="date"], input[type="number"], select {
            padding: 11px 13px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 14px; color: var(--text-main);
            background: #f8fbff; transition: border-color .2s, box-shadow .2s; width: 100%;
        }
        input:focus, select:focus {
            outline: none; border-color: var(--blue-light);
            box-shadow: 0 0 0 3px rgba(59,130,246,.12); background: #fff;
        }
        select:disabled { background: #f0f4fa; color: var(--text-muted); cursor: not-allowed; }

        .hint-text { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

        .section-label {
            font-size: 13px; font-weight: 600; color: var(--text-sub);
            text-transform: uppercase; letter-spacing: .4px;
            margin-top: 18px; margin-bottom: 10px;
        }

        .btn-primary {
            background: var(--blue-mid); color: #fff; border: none;
            padding: 13px 28px; border-radius: 9px; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: background .2s, transform .1s;
            width: 100%; margin-top: 14px;
        }
        .btn-primary:hover  { background: var(--blue-dark); }
        .btn-primary:active { transform: scale(.98); }

        .hasil-badge {
            padding: 14px 20px; border-radius: 10px; font-size: 15px;
            font-weight: 700; border-left: 5px solid; margin-top: 16px;
        }
        .hasil-badge.tinggi { background: #ecfdf5; border-color: #10b981; color: #064e3b; }
        .hasil-badge.sedang { background: #fffbeb; border-color: #f59e0b; color: #78350f; }
        .hasil-badge.rendah { background: #fef2f2; border-color: #ef4444; color: #7f1d1d; }

        .divider { border: none; border-top: 1.5px dashed var(--border); margin: 28px 0; }

        .period-toggle { display: flex; gap: 10px; flex-wrap: wrap; }
        .period-toggle label {
            flex: 1; min-width: 130px; border: 2px solid var(--border);
            border-radius: 9px; padding: 12px 16px; cursor: pointer;
            text-align: center; font-weight: 600; font-size: 14px;
            color: var(--text-sub); transition: all .2s; user-select: none;
        }
        .period-toggle input[type="radio"] { display: none; }
        .period-toggle input[type="radio"]:checked + label {
            border-color: var(--blue-mid); background: var(--blue-mid); color: #fff;
        }

        .chart-wrap { position: relative; width: 100%; height: 320px; }

        .legend-row  { display: flex; flex-wrap: wrap; gap: 18px; margin-bottom: 18px; align-items: center; }
        .legend-item { display: flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 600; color: var(--text-sub); }
        .legend-dot  { width: 12px; height: 12px; border-radius: 50%; }

        .analisis-box {
            background: #f0f6ff; border: 1.5px solid var(--border);
            border-radius: 12px; padding: 22px 24px; margin-top: 22px;
        }
        .analisis-box h4 {
            font-size: 15px; font-weight: 800; color: var(--blue-dark);
            margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
        }
        .analisis-box p {
            font-size: 14px; color: var(--text-sub); margin-bottom: 10px; line-height: 1.65;
        }
        .analisis-box p:last-child { margin-bottom: 0; }

        .trend-badge {
            display: inline-block; padding: 3px 12px;
            border-radius: 20px; font-size: 12px; font-weight: 700; margin-left: 6px;
        }
        .trend-linear-kuat               { background: #ecfdf5; color: #065f46; }
        .trend-linear-moderat            { background: #eff6ff; color: #1e40af; }
        .trend-non-linear---fluktuatif   { background: #fffbeb; color: #92400e; }
        .trend-sangat-tidak-beraturan    { background: #fef2f2; color: #991b1b; }

        .pred-nominal {
            font-size: 20px; font-weight: 800; color: var(--pred-color); margin: 0 6px;
        }
        .lr-class-badge {
            display: inline-block; background: var(--blue-mid); color: #fff;
            padding: 2px 12px; border-radius: 20px; font-size: 13px; margin: 0 4px;
        }

        /* Kartu evaluasi model (MSE) */
        .eval-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px; margin-top: 4px;
        }
        .eval-item {
            background: #f8fbff; border: 1.5px solid var(--border); border-radius: 10px;
            padding: 14px 16px; text-align: center;
        }
        .eval-item .eval-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .4px; color: var(--text-muted); margin-bottom: 6px;
        }
        .eval-item .eval-value { font-size: 17px; font-weight: 800; color: var(--blue-dark); }
        .eval-item.train .eval-value { color: var(--green); }
        .eval-item.test  .eval-value { color: var(--amber); }
        .eval-note { font-size: 12px; color: var(--text-muted); margin-top: 12px; line-height: 1.6; }

        /* Bar fitur paling berpengaruh */
        .fit-probs         { margin: 12px 0 14px; }
        .fit-prob-row       { display: flex; align-items: center; gap: 10px; margin-bottom: 7px; }
        .fit-label         { width: 150px; font-size: 12.5px; font-weight: 600; color: var(--text-sub); flex-shrink: 0; }
        .fit-bar-wrap      { flex: 1; height: 14px; background: #e2e8f0; border-radius: 7px; overflow: hidden; }
        .fit-bar           { height: 100%; border-radius: 7px; }
        .fit-pct           { width: 90px; text-align: right; font-size: 12.5px; font-weight: 700; color: var(--text-main); }

        .formula-note     { font-size: 12px !important; color: var(--text-muted) !important; font-style: italic; }

        /* Penjelasan asal rata-rata (rumus MSE/RMSE bertahap) */
        .rumus-box {
            background: #eef4ff; border: 1.5px dashed var(--blue-light);
            border-radius: 12px; padding: 18px 20px; margin-top: 16px;
        }
        .rumus-box h5 {
            font-size: 13.5px; font-weight: 800; color: var(--blue-dark);
            margin-bottom: 10px; display: flex; align-items: center; gap: 7px;
        }
        .rumus-langkah {
            display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;
        }
        .rumus-baris {
            display: flex; align-items: baseline; gap: 8px; font-size: 13px;
            background: #fff; border: 1px solid var(--border); border-radius: 8px;
            padding: 9px 12px; flex-wrap: wrap;
        }
        .rumus-baris .rumus-tag {
            flex-shrink: 0; font-weight: 800; font-size: 11px; color: #fff;
            background: var(--blue-mid); padding: 2px 8px; border-radius: 6px;
        }
        .rumus-baris code {
            font-family: 'Consolas', monospace; font-size: 12.5px; color: var(--text-main);
            background: #f5f9ff; padding: 2px 6px; border-radius: 5px;
        }
        .rumus-baris .rumus-hasil { font-weight: 700; color: var(--blue-dark); margin-left: auto; }
        .rumus-set-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
        }

        /* Tabel data latih & uji */
        .set-toggle { display: flex; gap: 10px; flex-wrap: wrap; margin: 4px 0 14px; }
        .set-toggle label {
            border: 2px solid var(--border); border-radius: 20px; padding: 7px 16px;
            cursor: pointer; font-weight: 700; font-size: 12.5px; color: var(--text-sub);
            transition: all .2s; user-select: none;
        }
        .set-toggle input[type="radio"] { display: none; }
        .set-toggle input#tab_latih:checked  + label { border-color: var(--green); background: var(--green); color: #fff; }
        .set-toggle input#tab_uji:checked    + label { border-color: var(--amber); background: var(--amber); color: #fff; }
        .set-toggle input#tab_semua:checked  + label { border-color: var(--blue-mid); background: var(--blue-mid); color: #fff; }
        .data-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid var(--border); max-height: 420px; overflow-y: auto; }
        .data-table { width: 100%; border-collapse: collapse; min-width: 680px; font-size: 12.5px; }
        .data-table thead tr { background: var(--blue-dark); color: #fff; position: sticky; top: 0; }
        .data-table thead th { padding: 9px 11px; text-align: left; white-space: nowrap; font-weight: 600; }
        .data-table tbody tr:nth-child(even) { background: #f5f9ff; }
        .data-table tbody tr:hover { background: #dbeafe; }
        .data-table tbody td { padding: 8px 11px; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .set-pill { display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 11px; font-weight: 700; }
        .set-pill.latih { background: #ecfdf5; color: #065f46; }
        .set-pill.uji   { background: #fffbeb; color: #92400e; }
        .err-pill { display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 11px; font-weight: 700; }
        .err-good { background: #ecfdf5; color: #065f46; }
        .err-mid  { background: #fffbeb; color: #92400e; }
        .err-bad  { background: #fef2f2; color: #991b1b; }
        .row-hidden { display: none; }

        /* Tabel */
        .info-filter { font-size: 13px; color: var(--text-sub); margin-bottom: 14px; }
        .info-filter span { font-weight: 700; color: var(--blue-mid); }
        .tabel-wrap  { overflow-x: auto; border-radius: 10px; border: 1px solid var(--border); }
        table        { width: 100%; border-collapse: collapse; min-width: 750px; font-size: 13px; }
        thead tr     { background: var(--blue-dark); color: #fff; }
        thead th     { padding: 11px 13px; text-align: left; white-space: nowrap; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f5f9ff; }
        tbody tr:hover           { background: #dbeafe; }
        tbody td     { padding: 10px 13px; border-bottom: 1px solid var(--border); white-space: nowrap; }

        .badge       { display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 12px; font-weight: 700; }
        .badge-ya    { background: #ecfdf5; color: #065f46; }
        .badge-tidak { background: #fef2f2; color: #991b1b; }
        .nominal     { font-weight: 700; color: var(--blue-dark); }
        .empty-state { text-align: center; padding: 36px; color: var(--text-muted); font-size: 14px; }
        .jumlah-data { text-align: right; font-size: 12px; color: var(--text-muted); margin-top: 10px; }

        @media (max-width: 600px) {
            .card { padding: 20px 16px; }
            .form-grid, .form-grid3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="page-wrap">

    <a href="about.php" class="back-btn">← Kembali Dashboard</a>

    <h1 class="page-title">
        Prediksi AI Donasi
        <span class="lr-pill">📐 Regresi Linear</span>
    </h1>
    <p class="page-sub">Analisis potensi &amp; estimasi nominal donasi dengan model Regresi Linear (Berganda + Tren Waktu)</p>

    <!-- ══════════════════════════════════════════════
         KARTU EVALUASI MODEL (MSE + TABEL DATA LATIH/UJI)
    ══════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-title">
            <span class="icon">🧪</span>
            Evaluasi Model Regresi Linear Berganda
        </div>
        <p class="hint-text" style="margin-bottom:14px;">
            Dataset (<?= $lr_eval['n_total'] ?> baris) dibagi secara <strong>kronologis berdasarkan tanggal</strong>
            (bukan acak) menjadi <strong>Data Latih (80% = <?= $lr_eval['n_train'] ?> baris, periode lebih lama)</strong>
            dan <strong>Data Uji (20% = <?= $lr_eval['n_test'] ?> baris, periode paling baru)</strong>.
            Model dilatih hanya dari data latih, lalu diuji pada data uji agar hasil evaluasi jujur (tidak overfit)
            dan mensimulasikan skenario nyata: memprediksi periode yang belum pernah dilihat model.
        </p>

        <p class="section-label" style="margin-top:4px;">Rentang Tanggal Tiap Set</p>
        <div class="eval-grid">
            <div class="eval-item train">
                <div class="eval-label">Data Latih (Lama)</div>
                <div class="eval-value" style="font-size:14px;">
                    <?= date('d M Y', strtotime($lr_eval['train_tgl_awal'])) ?>
                    &ndash;
                    <?= date('d M Y', strtotime($lr_eval['train_tgl_akhir'])) ?>
                </div>
            </div>
            <div class="eval-item test">
                <div class="eval-label">Data Uji (Terbaru)</div>
                <div class="eval-value" style="font-size:14px;">
                    <?= date('d M Y', strtotime($lr_eval['test_tgl_awal'])) ?>
                    &ndash;
                    <?= date('d M Y', strtotime($lr_eval['test_tgl_akhir'])) ?>
                </div>
            </div>
        </div>

        <p class="section-label" style="margin-top:22px;">Rata-rata Selisih Prediksi (RMSE)</p>
        <div class="eval-grid">
            <div class="eval-item train">
                <div class="eval-label">Rata-rata Selisih (MSE) — Latih</div>
                <div class="eval-value">Rp <?= number_format($lr_eval['rmse_train'], 0, ',', '.') ?></div>
            </div>
            <div class="eval-item test">
                <div class="eval-label">Rata-rata Selisih (MSE) — Uji</div>
                <div class="eval-value">Rp <?= number_format($lr_eval['rmse_test'], 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- ⬇️ TABEL REKAP PERHITUNGAN RATA-RATA (MSE) — terpisah dari tabel detail per-baris ⬇️ -->
        <?php if (!empty($lr_detail_all)): ?>
        <p class="section-label" style="margin-top:22px;">Rekap Perhitungan Rata-rata (MSE) per Set</p>
        <p class="hint-text" style="margin-bottom:10px;">
            Tabel ini merangkum <strong>data mana saja</strong> (Latih / Uji) yang dipakai untuk menghasilkan
            angka rata-rata pada kartu di atas, beserta komponen perhitungannya:
            MSE = (Total Kuadrat Selisih) ÷ (Jumlah Data), lalu RMSE = √MSE.
        </p>
        <div class="tabel-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Set Data</th>
                        <th>Rentang Tanggal</th>
                        <th>Jumlah Data (n)</th>
                        <th>Total Kuadrat Selisih — Σ(Aktual − Prediksi)²</th>
                        <th>MSE (Total ÷ n)</th>
                        <th>RMSE (√MSE)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="set-pill latih">Latih</span></td>
                        <td><?= date('d M Y', strtotime($lr_eval['train_tgl_awal'])) ?> &ndash; <?= date('d M Y', strtotime($lr_eval['train_tgl_akhir'])) ?></td>
                        <td><?= number_format($lr_eval['n_train'], 0, ',', '.') ?> baris</td>
                        <td><?= number_format($lr_eval['sumsq_train'], 0, ',', '.') ?></td>
                        <td><?= number_format($lr_eval['mse_train'], 0, ',', '.') ?></td>
                        <td class="nominal">Rp <?= number_format($lr_eval['rmse_train'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td><span class="set-pill uji">Uji</span></td>
                        <td><?= date('d M Y', strtotime($lr_eval['test_tgl_awal'])) ?> &ndash; <?= date('d M Y', strtotime($lr_eval['test_tgl_akhir'])) ?></td>
                        <td><?= number_format($lr_eval['n_test'], 0, ',', '.') ?> baris</td>
                        <td><?= number_format($lr_eval['sumsq_test'], 0, ',', '.') ?></td>
                        <td><?= number_format($lr_eval['mse_test'], 0, ',', '.') ?></td>
                        <td class="nominal">Rp <?= number_format($lr_eval['rmse_test'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ⬇️ PENJELASAN ASAL RATA-RATA (langkah perhitungan MSE → RMSE, dengan angka nyata) ⬇️ -->
        <div class="rumus-box">
            <h5>🔎 Dari Mana Asal Angka Rata-rata Ini?</h5>
            <p class="hint-text" style="margin-bottom:12px;">
                Angka "rata-rata selisih" pada kartu di atas bukan rata-rata biasa (jumlah dibagi banyaknya data),
                melainkan dihitung lewat beberapa langkah berikut — supaya selisih yang besar "dihukum" lebih berat
                daripada selisih yang kecil:
            </p>
            <div class="rumus-langkah">
                <div class="rumus-baris">
                    <span class="rumus-tag">Langkah 1</span>
                    Untuk setiap baris data, hitung selisih antara nominal <strong>aktual</strong> dan nominal
                    <strong>hasil prediksi model</strong>, lalu <strong>kuadratkan</strong> selisihnya
                    (supaya nilai negatif tidak saling meniadakan, dan selisih besar dihukum lebih berat).
                    <br><code>selisih² = (aktual − prediksi)²</code>
                </div>
                <div class="rumus-baris">
                    <span class="rumus-tag">Langkah 2</span>
                    <strong>Jumlahkan</strong> seluruh nilai selisih² dari semua baris dalam satu set data
                    (Latih dijumlah sendiri, Uji dijumlah sendiri) — inilah kolom
                    "Total Kuadrat Selisih" pada tabel di atas.
                    <br><code>Total = Σ (aktual − prediksi)²</code>
                </div>
                <div class="rumus-baris">
                    <span class="rumus-tag">Langkah 3</span>
                    <strong>Bagi</strong> Total tersebut dengan jumlah baris (n) pada set itu → hasilnya disebut
                    <strong>MSE</strong> (Mean Squared Error / rata-rata kuadrat selisih).
                    <br><code>MSE = Total ÷ n</code>
                </div>
                <div class="rumus-baris">
                    <span class="rumus-tag">Langkah 4</span>
                    Karena satuan MSE adalah "Rupiah kuadrat" (susah dibaca), diambil <strong>akar kuadratnya</strong>
                    agar kembali ke satuan Rupiah biasa. Inilah <strong>RMSE</strong> yang ditampilkan sebagai
                    "Rata-rata Selisih" pada kartu paling atas.
                    <br><code>RMSE = √MSE</code>
                </div>
            </div>

            <p class="hint-text" style="margin-bottom:8px;font-weight:700;color:var(--text-sub);">
                Contoh perhitungan nyata dari data di halaman ini:
            </p>
            <div class="rumus-set-grid">
                <div class="rumus-baris" style="flex-direction:column;align-items:flex-start;">
                    <span class="rumus-tag" style="margin-bottom:4px;">Set Latih</span>
                    <code>MSE = <?= number_format($lr_eval['sumsq_train'], 0, ',', '.') ?> ÷ <?= $lr_eval['n_train'] ?> = <?= number_format($lr_eval['mse_train'], 0, ',', '.') ?></code>
                    <code>RMSE = √<?= number_format($lr_eval['mse_train'], 0, ',', '.') ?> ≈ Rp <?= number_format($lr_eval['rmse_train'], 0, ',', '.') ?></code>
                </div>
                <div class="rumus-baris" style="flex-direction:column;align-items:flex-start;">
                    <span class="rumus-tag" style="margin-bottom:4px;">Set Uji</span>
                    <code>MSE = <?= number_format($lr_eval['sumsq_test'], 0, ',', '.') ?> ÷ <?= $lr_eval['n_test'] ?> = <?= number_format($lr_eval['mse_test'], 0, ',', '.') ?></code>
                    <code>RMSE = √<?= number_format($lr_eval['mse_test'], 0, ',', '.') ?> ≈ Rp <?= number_format($lr_eval['rmse_test'], 0, ',', '.') ?></code>
                </div>
            </div>

            <p class="eval-note" style="margin-top:12px;">
                Nilai "selisih² per baris" pada Langkah 1 sebenarnya adalah kuadrat dari kolom
                "Selisih (Rp)" yang bisa dilihat satu per satu pada tabel "Isi Data Latih &amp; Data Uji" di bawah.
                Karena RMSE dihitung dari <em>akar rata-rata kuadrat</em> (bukan rata-rata polos), baris dengan
                selisih sangat besar akan menaikkan RMSE lebih tinggi dibanding pengaruhnya pada rata-rata biasa —
                itu sebabnya angka RMSE biasanya sedikit lebih besar daripada rata-rata sederhana kolom
                "Selisih (Rp)".
            </p>
        </div>
        <!-- ⬆️ SELESAI PENJELASAN ASAL RATA-RATA ⬆️ -->

        <p class="eval-note" style="margin-top:10px;">
            Total Kuadrat Selisih pada baris <strong>Latih</strong> dijumlahkan dari <?= $lr_eval['n_train'] ?> baris
            Data Latih, dan pada baris <strong>Uji</strong> dijumlahkan dari <?= $lr_eval['n_test'] ?> baris Data Uji.
            Rincian tiap barisnya (kategori, nominal aktual, estimasi model, dan selisihnya satu per satu) bisa
            dilihat pada tabel "Isi Data Latih &amp; Data Uji" di bawah ini.
        </p>
        <?php endif; ?>
        <!-- ⬆️ SELESAI TABEL REKAP PERHITUNGAN RATA-RATA ⬆️ -->

        <!-- ⬇️ TABEL ISI DATA LATIH & UJI — detail per-baris (terpisah dari tabel rekap di atas) ⬇️ -->
        <?php if (!empty($lr_detail_all)): ?>
        <p class="section-label" style="margin-top:28px;">Isi Data Latih &amp; Data Uji</p>
        <div class="set-toggle">
            <input type="radio" name="tab_set" id="tab_semua" checked>
            <label for="tab_semua" onclick="filterSet('semua')">Semua (<?= count($lr_detail_all) ?>)</label>

            <input type="radio" name="tab_set" id="tab_latih">
            <label for="tab_latih" onclick="filterSet('latih')">Data Latih (<?= $lr_eval['n_train'] ?>)</label>

            <input type="radio" name="tab_set" id="tab_uji">
            <label for="tab_uji" onclick="filterSet('uji')">Data Uji (<?= $lr_eval['n_test'] ?>)</label>
        </div>
        <div class="data-table-wrap">
            <table class="data-table" id="tabelLatihUji">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Set</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Jenis Donasi</th>
                        <th>Metode</th>
                        <th>Nominal Aktual</th>
                        <th>Estimasi Model</th>
                        <th>Selisih (Rp)</th>
                        <th>Selisih (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lr_detail_all as $i => $d):
                        $err = $d['error_pct'];
                        $err_class = $err <= 10 ? 'err-good' : ($err <= 25 ? 'err-mid' : 'err-bad');
                        $set_slug  = $d['set'] === 'Latih' ? 'latih' : 'uji';
                    ?>
                    <tr class="row-set-<?= $set_slug ?>">
                        <td><?= $i + 1 ?></td>
                        <td><span class="set-pill <?= $set_slug ?>"><?= $d['set'] ?></span></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($d['ctx']['tanggal']))) ?></td>
                        <td><?= htmlspecialchars($d['ctx']['kategori']) ?></td>
                        <td><?= htmlspecialchars($d['ctx']['jenis']) ?></td>
                        <td><?= htmlspecialchars($d['ctx']['metode'] ?: '-') ?></td>
                        <td class="nominal">Rp <?= number_format((int)$d['aktual'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format((int)$d['prediksi'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format((int)$d['selisih_rp'], 0, ',', '.') ?></td>
                        <td><span class="err-pill <?= $err_class ?>"><?= number_format($err, 1) ?>%</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="jumlah-data">Menampilkan <b><?= count($lr_detail_all) ?></b> baris (Latih + Uji)</p>
        <p class="eval-note" style="margin-top:10px;text-align:right;">
            Kolom "Selisih (Rp)" pada <?= $lr_eval['n_train'] ?> baris <strong>Data Latih</strong> dan
            <?= $lr_eval['n_test'] ?> baris <strong>Data Uji</strong> di atas adalah sumber perhitungan
            rata-rata pada kartu evaluasi (Rp <?= number_format($lr_eval['rmse_train'], 0, ',', '.') ?> untuk
            Latih, Rp <?= number_format($lr_eval['rmse_test'], 0, ',', '.') ?> untuk Uji) — namun angka di kartu
            dihitung lewat rata-rata kuadrat (RMSE), bukan rata-rata sederhana, sehingga akan sedikit berbeda dari
            rata-rata polos kolom ini.
        </p>
        <?php endif; ?>
        <!-- ⬆️ SELESAI TABEL DETAIL ISI DATA LATIH & UJI ⬆️ -->

        <p class="eval-note" style="margin-top:20px;">
            Angka di atas adalah <strong>rata-rata selisih (dalam Rupiah)</strong> antara nominal aktual dan nominal
            hasil prediksi model, diturunkan dari MSE (Mean Squared Error) lalu diakarkan agar satuannya kembali ke
            Rupiah dan mudah dibaca. Semakin kecil nilainya, semakin dekat prediksi model dengan data sebenarnya.
            Jika angka pada <em>Data Uji</em> jauh lebih besar dibanding <em>Data Latih</em>, model kemungkinan
            overfit — hafal pola data latih tapi kurang mampu menggeneralisasi ke data baru.
        </p>
        <p class="eval-note">
            Baris mana saja dari <?= $lr_eval['n_total'] ?> data yang termasuk Data Latih (<?= $lr_eval['n_train'] ?> baris)
            dan Data Uji (<?= $lr_eval['n_test'] ?> baris) — beserta selisihnya masing-masing yang menyusun rata-rata
            di atas — bisa dilihat pada tabel "Isi Data Latih &amp; Data Uji" di atas.
        </p>
        <p class="eval-note">
            Dengan jumlah data sebesar <?= $lr_eval['n_total'] ?> baris, pembagian 80/20 menghasilkan
            <?= $lr_eval['n_train'] ?> baris data latih dan <?= $lr_eval['n_test'] ?> baris data uji — cukup untuk
            melatih model regresi ini, meski hasil evaluasi tetap perlu dibaca sebagai estimasi kasar karena ukuran
            data yang relatif kecil.
        </p>

        <?php if (!empty($lr_top_fit)): ?>
        <p class="section-label" style="margin-top:20px;">Fitur Paling Berpengaruh Terhadap Nominal Donasi</p>
        <div class="fit-probs">
            <?php
                $max_coef = max(array_map(fn($f) => abs($f['coef']), $lr_top_fit)) ?: 1;
                foreach ($lr_top_fit as $f):
                    $pct   = round((abs($f['coef']) / $max_coef) * 100, 1);
                    $color = $f['coef'] >= 0 ? '#10b981' : '#ef4444';
                    $sign  = $f['coef'] >= 0 ? '+' : '-';
            ?>
                <div class="fit-prob-row">
                    <span class="fit-label"><?= htmlspecialchars($f['name']) ?></span>
                    <div class="fit-bar-wrap">
                        <div class="fit-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                    </div>
                    <span class="fit-pct"><?= $sign ?><?= number_format(min($pct, 100), 1) ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="eval-note">Panjang bar menunjukkan seberapa besar pengaruh relatif tiap fitur dibanding fitur referensi (dinormalisasi ke skala 0–100%).</p>
        <?php endif; ?>

        <?php if (empty($lr_detail_all)): ?>
        <p class="empty-state">⚠️ Data belum cukup untuk melatih model (minimal 10 baris).</p>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════
         FORM 1 : Prediksi Kategori
    ══════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-title">
            <span class="icon">🏷️</span>
            Prediksi Berdasarkan Kategori
        </div>

        <form method="POST">
            <div class="form-grid">
                <div class="field">
                    <label class="field-label">Kategori Donasi</label>
                    <select name="kategori" id="select_kategori" required onchange="filterJenis()">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($semua_kategori as $kat): ?>
                            <option value="<?= htmlspecialchars($kat) ?>"
                                <?= (isset($_POST['kategori']) && $_POST['kategori'] === $kat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label">Jenis Donasi</label>
                    <select name="jenis_donasi" id="select_jenis" required disabled>
                        <option value="">-- Pilih Kategori dulu --</option>
                    </select>
                    <span class="hint-text">Muncul otomatis sesuai kategori</span>
                </div>
            </div>

            <button type="submit" name="prediksi" class="btn-primary">🔍 Prediksi Potensi</button>
        </form>

        <?php if ($hasil !== ''): ?>
            <?php
                $kelas_hasil = 'rendah';
                if (str_contains($hasil, 'Tinggi')) $kelas_hasil = 'tinggi';
                elseif (str_contains($hasil, 'Sedang')) $kelas_hasil = 'sedang';
            ?>
            <div class="hasil-badge <?= $kelas_hasil ?>">
                Hasil Prediksi: <?= $hasil ?>
            </div>

            <div style="margin-top:20px;">
                <p class="info-filter">
                    Kategori: <span><?= htmlspecialchars($_POST['kategori']) ?></span>
                    &nbsp;|&nbsp;
                    Jenis: <span><?= htmlspecialchars($_POST['jenis_donasi']) ?></span>
                </p>

                <?php if (count($data_filter) > 0): ?>
                    <div class="tabel-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <?php foreach ($label_kolom as $col => $label): ?>
                                        <th><?= $label ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data_filter as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <?php foreach ($label_kolom as $col => $label): ?>
                                            <td>
                                                <?php
                                                $val = htmlspecialchars($row[$col] ?? '-');
                                                if ($col === 'nominal_donasi') {
                                                    echo '<span class="nominal">Rp ' . number_format((int)$row[$col], 0, ',', '.') . '</span>';
                                                } elseif (in_array($col, ['donasi_rutin', 'pernah_donasi'])) {
                                                    $badge = strtolower($row[$col]) === 'ya' ? 'badge-ya' : 'badge-tidak';
                                                    echo "<span class='badge $badge'>$val</span>";
                                                } else {
                                                    echo $val;
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="jumlah-data">Total: <b><?= count($data_filter) ?></b> data ditemukan</p>
                <?php else: ?>
                    <div class="empty-state">⚠️ Tidak ada data untuk kombinasi ini.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <hr class="divider">

    <!-- ══════════════════════════════════════════════
         FORM 2 : Prediksi Seri Waktu + Regresi Linear
    ══════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-title">
            <span class="icon">📅</span>
            Prediksi Nominal Donasi per Waktu
            <span class="lr-pill">Regresi Linear</span>
        </div>

        <form method="POST">
            <div class="form-grid">
                <div class="field">
                    <label class="field-label">Tanggal Referensi</label>
                    <input type="date" name="tanggal_awal"
                           value="<?= htmlspecialchars($input_tanggal ?: date('Y-m-d')) ?>" required>
                    <span class="hint-text">Titik awal prediksi</span>
                </div>
                <div class="field">
                    <label class="field-label">Estimasi Nominal (Rp)</label>
                    <input type="number" name="nominal_input" min="0" step="10000"
                           placeholder="Contoh: 2500000"
                           value="<?= $input_nominal > 0 ? $input_nominal : '' ?>" required>
                    <span class="hint-text">Perkiraan Anda untuk periode tersebut</span>
                </div>
            </div>

            <!-- Konteks untuk Regresi Linear Berganda -->
            <p class="section-label">Konteks Regresi Linear <em style="font-weight:400;text-transform:none;">(opsional, meningkatkan akurasi)</em></p>
            <div class="form-grid3">
                <div class="field">
                    <label class="field-label">Kategori</label>
                    <select name="filter_kategori">
                        <option value="">-- Semua --</option>
                        <?php foreach ($semua_kategori as $kat): ?>
                            <option value="<?= htmlspecialchars($kat) ?>"
                                <?= ($input_kat === $kat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label class="field-label">Metode Bayar</label>
                    <select name="filter_metode">
                        <option value="">-- Semua --</option>
                        <?php foreach ($semua_metode as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>"
                                <?= ($input_metode === $m) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label class="field-label">Donasi Rutin?</label>
                    <select name="filter_rutin">
                        <option value="">-- Semua --</option>
                        <option value="ya"    <?= ($input_rutin === 'ya')    ? 'selected' : '' ?>>Ya</option>
                        <option value="tidak" <?= ($input_rutin === 'tidak') ? 'selected' : '' ?>>Tidak</option>
                    </select>
                </div>
            </div>

            <div class="field" style="margin-top:16px;">
                <label class="field-label">Tanggal yang Ingin Diprediksi</label>
                <input type="date" name="tanggal_prediksi" id="tanggal_prediksi"
                       value="<?= htmlspecialchars($input_tgl_target ?: date('Y-m-d', strtotime('+1 day'))) ?>" required>
                <span class="hint-text" id="hint_selisih">Pilih tanggal berapapun setelah Tanggal Referensi — sistem akan memprediksi nominal persis untuk hari itu</span>
            </div>

            <button type="submit" name="prediksi_waktu" class="btn-primary">
                📊 Tampilkan Grafik &amp; Prediksi Regresi
            </button>
        </form>
    </div>

    <!-- ══════════════════════════════════════════════
         OUTPUT GRAFIK + ANALISIS
    ══════════════════════════════════════════════ -->
    <?php if (!empty($chart_labels)): ?>
    <div class="card">
        <div class="card-title">
            <span class="icon">📈</span>
            Grafik Donasi &amp; Prediksi
            <?php if ($trend_type): ?>
                <?php $slug = strtolower(str_replace([' ', '/'], ['-', '-'], $trend_type)); ?>
                <span class="trend-badge trend-<?= $slug ?>">
                    <?= htmlspecialchars($trend_type) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="legend-row">
            <div class="legend-item">
                <div class="legend-dot" style="background:var(--aktual-color);"></div>
                Aktual Historis
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background:var(--pred-color);"></div>
                Prediksi Regresi Linear
            </div>
        </div>

        <div class="chart-wrap">
            <canvas id="chartWaktu"></canvas>
        </div>

        <?php if ($analisis_teks): ?>
        <div class="analisis-box">
            <h4>📐 Analisis Regresi Linear</h4>
            <?= $analisis_teks ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /page-wrap -->

<script>
/* ── Dropdown kategori → jenis ─────────────────────────────────────────── */
const kategoriJenisMap = <?= json_encode($kategori_jenis_map, JSON_UNESCAPED_UNICODE) ?>;
const selectedKategori = "<?= isset($_POST['kategori'])     ? addslashes($_POST['kategori'])     : '' ?>";
const selectedJenis    = "<?= isset($_POST['jenis_donasi']) ? addslashes($_POST['jenis_donasi']) : '' ?>";

function filterJenis() {
    const sk = document.getElementById('select_kategori');
    const sj = document.getElementById('select_jenis');
    const k  = sk.value;
    sj.innerHTML = '';
    if (!k || !kategoriJenisMap[k]) {
        sj.innerHTML = '<option value="">-- Pilih Kategori dulu --</option>';
        sj.disabled  = true;
        return;
    }
    sj.disabled  = false;
    sj.innerHTML = '<option value="">-- Pilih Jenis Donasi --</option>';
    kategoriJenisMap[k].forEach(j => {
        const o = document.createElement('option');
        o.value = j; o.textContent = j;
        if (j === selectedJenis) o.selected = true;
        sj.appendChild(o);
    });
}

/* ── Validasi & info jarak hari untuk Tanggal yang Ingin Diprediksi ──────── */
function updateTanggalPrediksi() {
    const ref    = document.querySelector('input[name="tanggal_awal"]');
    const target = document.getElementById('tanggal_prediksi');
    const hint   = document.getElementById('hint_selisih');
    if (!ref || !target || !ref.value) return;

    const refDate = new Date(ref.value + 'T00:00:00');
    const minDate = new Date(refDate);
    minDate.setDate(minDate.getDate() + 1);
    const minStr = minDate.toISOString().split('T')[0];
    target.min = minStr;

    if (!target.value || target.value < minStr) target.value = minStr;

    const targetDate = new Date(target.value + 'T00:00:00');
    const selisih = Math.round((targetDate - refDate) / 86400000);
    if (hint) {
        hint.textContent = selisih >= 1
            ? `Diprediksi untuk ${selisih} hari setelah tanggal referensi`
            : 'Pilih tanggal setelah Tanggal Referensi';
    }
}

/* ── Toggle tabel Data Latih / Data Uji ──────────────────────────────────── */
function filterSet(mode) {
    const rows = document.querySelectorAll('#tabelLatihUji tbody tr');
    rows.forEach(r => {
        if (mode === 'semua') { r.classList.remove('row-hidden'); return; }
        const isMatch = r.classList.contains('row-set-' + mode);
        r.classList.toggle('row-hidden', !isMatch);
    });
}

/* ── Chart.js ─────────────────────────────────────────────────────────── */
window.addEventListener('DOMContentLoaded', () => {
    if (selectedKategori) {
        document.getElementById('select_kategori').value = selectedKategori;
        filterJenis();
    }

    updateTanggalPrediksi();
    document.querySelector('input[name="tanggal_awal"]')?.addEventListener('change', updateTanggalPrediksi);
    document.getElementById('tanggal_prediksi')?.addEventListener('change', updateTanggalPrediksi);

    const canvas = document.getElementById('chartWaktu');
    if (!canvas) return;

    const labels   = <?= json_encode($chart_labels) ?>;
    const aktual   = <?= json_encode($chart_aktual) ?>;
    const prediksi = <?= json_encode($chart_prediksi) ?>;

    // Hitung batas Y agar kedua garis selalu terlihat dalam range yang sama
    const allVals = [...aktual, ...prediksi].filter(v => v !== null && v !== undefined);
    const minVal  = Math.min(...allVals);
    const maxVal  = Math.max(...allVals);
    const padding = Math.max((maxVal - minVal) * 0.18, maxVal * 0.12, 200000);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Aktual Historis',
                    data: aktual,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,.10)',
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.35,
                    spanGaps: false,
                },
                {
                    label: 'Prediksi Regresi Linear',
                    data: prediksi,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.07)',
                    borderWidth: 2.5,
                    borderDash: [7, 4],
                    // Titik terakhir (prediksi ke depan) lebih besar
                    pointRadius: ctx => ctx.dataIndex === labels.length - 1 ? 9 : 5,
                    pointStyle: ctx => ctx.dataIndex === labels.length - 1 ? 'rectRot' : 'circle',
                    pointHoverRadius: 8,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f1f45',
                    titleColor: '#e2e8f0',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    callbacks: {
                        label(ctx) {
                            if (ctx.parsed.y === null) return null;
                            const suffix = ctx.datasetIndex === 1 &&
                                ctx.dataIndex === labels.length - 1 ? ' ← prediksi' : '';
                            return ctx.dataset.label + ': Rp ' +
                                Math.round(ctx.parsed.y).toLocaleString('id-ID') + suffix;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { font: { size: 12 }, color: '#4b5e8a' }
                },
                y: {
                    min: Math.max(0, minVal - padding),
                    max: maxVal + padding,
                    grid: { color: 'rgba(0,0,0,.06)' },
                    ticks: {
                        font: { size: 11 }, color: '#4b5e8a',
                        callback(v) {
                            if (v >= 1_000_000) return 'Rp ' + (v/1_000_000).toFixed(1) + 'jt';
                            if (v >= 1_000)     return 'Rp ' + (v/1_000).toFixed(0) + 'rb';
                            return 'Rp ' + v;
                        }
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>