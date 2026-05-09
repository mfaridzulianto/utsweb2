<?php
session_start();

if (!isset($_SESSION['registrants'])) {
    $_SESSION['registrants'] = [];
}

$errors = [];
$success = '';

function calculate_average($scores)
{
    return round(array_sum($scores) / count($scores), 2);
}

function determine_status($average)
{
    if ($average >= 70) {
        return 'Lulus';
    }
    if ($average >= 60) {
        return 'Cadangan';
    }
    return 'Tidak Lulus';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        $_SESSION['registrants'] = [];
        $success = 'Data pendaftaran telah dihapus.';
    } else {
        $kode = trim($_POST['kode_pendaftaran'] ?? '');
        $nama = trim($_POST['nama_pendaftar'] ?? '');
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
        $ttl = trim($_POST['ttl'] ?? '');
        $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
        $pekerjaan_ortu = trim($_POST['pekerjaan_ortu'] ?? '');
        $matematika = $_POST['matematika'] ?? '';
        $bahasa_inggris = $_POST['bahasa_inggris'] ?? '';
        $umum = $_POST['umum'] ?? '';

        if ($kode === '') {
            $errors[] = 'Kode pendaftaran harus diisi.';
        }
        if ($nama === '') {
            $errors[] = 'Nama pendaftar harus diisi.';
        }
        if ($jenis_kelamin === '') {
            $errors[] = 'Jenis kelamin harus dipilih.';
        }
        if ($ttl === '') {
            $errors[] = 'TTL harus diisi.';
        }
        if ($asal_sekolah === '') {
            $errors[] = 'Asal sekolah harus diisi.';
        }
        if ($pekerjaan_ortu === '') {
            $errors[] = 'Pekerjaan orang tua harus diisi.';
        }
        foreach (['matematika' => $matematika, 'bahasa_inggris' => $bahasa_inggris, 'umum' => $umum] as $field => $value) {
            if ($value === '' || !is_numeric($value) || $value < 0 || $value > 100) {
                $label = $field === 'matematika' ? 'Matematika' : ($field === 'bahasa_inggris' ? 'Bahasa Inggris' : 'Umum');
                $errors[] = "$label harus diisi dengan angka 0-100.";
            }
        }

        if (empty($errors)) {
            $scores = [
                'matematika' => (float) $matematika,
                'bahasa_inggris' => (float) $bahasa_inggris,
                'umum' => (float) $umum,
            ];

            $average = calculate_average($scores);
            $status = determine_status($average);

            $_SESSION['registrants'][] = [
                'kode' => htmlspecialchars($kode),
                'nama' => htmlspecialchars($nama),
                'jenis_kelamin' => htmlspecialchars($jenis_kelamin),
                'ttl' => htmlspecialchars($ttl),
                'asal_sekolah' => htmlspecialchars($asal_sekolah),
                'pekerjaan_ortu' => htmlspecialchars($pekerjaan_ortu),
                'matematika' => $scores['matematika'],
                'bahasa_inggris' => $scores['bahasa_inggris'],
                'umum' => $scores['umum'],
                'average' => $average,
                'status' => $status,
            ];

            $success = 'Data pendaftaran berhasil ditambahkan.';
            $_POST = [];
        }
    }
}

$registrants = $_SESSION['registrants'];
$total_registrants = count($registrants);
$average_scores = ['matematika' => 0, 'bahasa_inggris' => 0, 'umum' => 0];
if ($total_registrants > 0) {
    foreach ($registrants as $row) {
        $average_scores['matematika'] += $row['matematika'];
        $average_scores['bahasa_inggris'] += $row['bahasa_inggris'];
        $average_scores['umum'] += $row['umum'];
    }
    $average_scores = array_map(function ($sum) use ($total_registrants) {
        return round($sum / $total_registrants, 2);
    }, $average_scores);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pendaftaran Mahasiswa Baru</title>
    <style>
        :root {
            --bg: #f3f7ff;
            --card: #ffffff;
            --card-alt: #f6f9ff;
            --primary: #2853a1;
            --primary-soft: #e4efff;
            --accent: #3c8d87;
            --danger: #d64545;
            --text: #1e2d42;
            --muted: #65758b;
            --border: rgba(38, 63, 105, 0.12);
        }

        * {box-sizing: border-box;}
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent 35%),
                        linear-gradient(180deg, #eef5ff 0%, #f7fbff 100%);
            color: var(--text);
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .container {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px;
        }

        .hero {
            display: grid;
            gap: 16px;
            padding: 28px;
            background: linear-gradient(135deg, rgba(40, 83, 161, 0.95), rgba(59, 130, 246, 0.9));
            border-radius: 24px;
            color: #fff;
            box-shadow: 0 28px 60px rgba(8, 44, 109, 0.12);
            margin-bottom: 24px;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(2rem, 3vw, 3.2rem);
            line-height: 1.05;
        }

        .hero p {
            margin: 0;
            max-width: 760px;
            color: rgba(255,255,255,0.88);
            font-size: 1rem;
            line-height: 1.75;
        }

        .panel {
            display: grid;
            gap: 24px;
            background: rgba(255,255,255,0.9);
            border-radius: 28px;
            padding: 30px;
            box-shadow: 0 18px 40px rgba(79, 97, 143, 0.08);
        }

        .section-title {
            margin: 0 0 16px;
            font-size: 1.15rem;
            letter-spacing: 0.02em;
            color: var(--primary);
        }

        .grid {display: grid; gap: 18px; grid-template-columns: repeat(2, minmax(0, 1fr));}
        .field {display: flex; flex-direction: column; gap: 8px;}
        label {font-weight: 600; color: var(--text);}
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            color: var(--text);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            outline: none;
            border-color: rgba(40, 83, 161, 0.35);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .radio-group {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .radio-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card-alt);
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }

        .radio-card input {
            accent-color: var(--primary);
        }

        .radio-card:hover {transform: translateY(-1px);}
        .radio-card input:checked + span {font-weight: 700; color: var(--primary);}

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        button {
            min-width: 150px;
            padding: 14px 18px;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 20px rgba(42, 80, 148, 0.12);
        }

        button:hover {transform: translateY(-1px);}
        .btn-primary {background: var(--primary); color: #fff;}
        .btn-secondary {background: #fff; color: var(--primary); border: 1px solid rgba(40, 83, 161, 0.15);}
        .btn-danger {background: var(--danger); color: #fff;}

        .message {
            padding: 16px 20px;
            border-radius: 18px;
            margin-bottom: 20px;
            font-size: 0.98rem;
        }

        .message.success {background: #e8f7ee; color: #16653d;}
        .message.error {background: #ffeaea; color: #8f1e2f;}

        .cards, .summary {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .box {
            padding: 24px;
            background: #fff;
            border-radius: 24px;
            border: 1px solid rgba(41, 72, 161, 0.08);
            box-shadow: 0 10px 30px rgba(43, 83, 172, 0.08);
        }

        .box h3 {margin-top: 0; color: var(--primary);}
        .box p, .box li {margin: 0 0 10px; line-height: 1.7; color: var(--muted);}
        .box ul {padding-left: 18px; margin: 10px 0 0;}

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(40, 70, 130, 0.08);
        }

        th, td {
            padding: 16px 14px;
            text-align: left;
            border-bottom: 1px solid rgba(38, 63, 105, 0.08);
        }

        th {
            background: #eff4ff;
            color: #1e3a78;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 0.84rem;
        }

        tbody tr:hover {background: #f9fbff;}
        tbody tr:last-child td {border-bottom: none;}

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 98px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #fff;
        }

        .status-lulus {background: #2f855a;}
        .status-cadangan {background: #d69e2e;}
        .status-tidak-lulus {background: #c53030;}

        .footer-summary {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 20px;
            padding: 20px;
            border-radius: 20px;
            background: var(--card-alt);
            border: 1px solid rgba(40, 83, 161, 0.08);
        }

        .footer-summary div {flex: 1;}
        .footer-summary h3 {margin: 0 0 10px; color: var(--primary);}
        .footer-summary p {margin: 0 0 6px; color: var(--muted);}

        @media (max-width: 980px) {
            .grid, .cards, .footer-summary {grid-template-columns: 1fr;}
            .hero {padding: 24px;}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>Pendaftaran Mahasiswa Baru</h1>
            <p>Isi data peserta dan nilai dengan mudah. Sistem akan menghitung rata-rata dan menentukan keterangan kelulusan otomatis tanpa menggunakan database.</p>
        </div>

        <?php if ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="message error"><ul><?php foreach ($errors as $error) { echo '<li>' . $error . '</li>'; } ?></ul></div>
        <?php endif; ?>

        <div class="panel">
            <h2 class="section-title">Form Pendaftaran</h2>
            <form method="post">
                <div class="grid">
                    <div class="field">
                        <label for="kode_pendaftaran">Kode Pendaftaran</label>
                        <input type="text" id="kode_pendaftaran" name="kode_pendaftaran" value="<?= htmlspecialchars($_POST['kode_pendaftaran'] ?? '') ?>" placeholder="Contoh: A2-101-9" />
                    </div>
                    <div class="field">
                        <label for="nama_pendaftar">Nama Pendaftar</label>
                        <input type="text" id="nama_pendaftar" name="nama_pendaftar" value="<?= htmlspecialchars($_POST['nama_pendaftar'] ?? '') ?>" />
                    </div>
                    <div class="field">
                        <label>Jenis Kelamin</label>
                        <div class="radio-group">
                            <label class="radio-card"><input type="radio" name="jenis_kelamin" value="Laki-laki" <?= isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'Laki-laki' ? 'checked' : '' ?> /><span>Laki-laki</span></label>
                            <label class="radio-card"><input type="radio" name="jenis_kelamin" value="Perempuan" <?= isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'Perempuan' ? 'checked' : '' ?> /><span>Perempuan</span></label>
                        </div>
                    </div>
                    <div class="field">
                        <label for="ttl">TTL</label>
                        <input type="text" id="ttl" name="ttl" value="<?= htmlspecialchars($_POST['ttl'] ?? '') ?>" placeholder="Contoh: Jakarta, 1 Jan 2005" />
                    </div>
                    <div class="field">
                        <label for="asal_sekolah">Asal Sekolah</label>
                        <input type="text" id="asal_sekolah" name="asal_sekolah" value="<?= htmlspecialchars($_POST['asal_sekolah'] ?? '') ?>" />
                    </div>
                    <div class="field">
                        <label for="pekerjaan_ortu">Pekerjaan Orang Tua</label>
                        <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" value="<?= htmlspecialchars($_POST['pekerjaan_ortu'] ?? '') ?>" />
                    </div>
                    <div class="field">
                        <label for="matematika">Nilai Matematika</label>
                        <input type="number" id="matematika" name="matematika" min="0" max="100" value="<?= htmlspecialchars($_POST['matematika'] ?? '') ?>" />
                    </div>
                    <div class="field">
                        <label for="bahasa_inggris">Nilai Bahasa Inggris</label>
                        <input type="number" id="bahasa_inggris" name="bahasa_inggris" min="0" max="100" value="<?= htmlspecialchars($_POST['bahasa_inggris'] ?? '') ?>" />
                    </div>
                    <div class="field">
                        <label for="umum">Nilai Umum</label>
                        <input type="number" id="umum" name="umum" min="0" max="100" value="<?= htmlspecialchars($_POST['umum'] ?? '') ?>" />
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <button type="submit" name="reset" value="1" class="btn-danger">Hapus Data</button>
                </div>
            </form>
        </div>

        <div class="summary">
            <div class="box">
                <h3>Petunjuk Kode</h3>
                <p>2 karakter awal: lokasi tes / gelombang</p>
                <ul>
                    <li>A = Gedung A</li>
                    <li>B = Gedung B</li>
                    <li>V = Viktor</li>
                </ul>
                <p>1 karakter akhir: bulan tes</p>
                <p>Contoh kode: <strong>A2-101-9</strong></p>
            </div>
            <div class="box">
                <h3>Status Kelulusan</h3>
                <p>RATA-RATA = Matematika, Bahasa Inggris, Umum</p>
                <p><strong>&ge; 70:</strong> Lulus</p>
                <p><strong>60 - 69.99:</strong> Cadangan</p>
                <p><strong>&lt; 60:</strong> Tidak Lulus</p>
            </div>
        </div>

        <h2>Data Pendaftar</h2>
        <p>Jumlah pendaftar: <strong><?= $total_registrants ?></strong></p>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>JK</th>
                    <th>TTL</th>
                    <th>Asal Sekolah</th>
                    <th>Pekerjaan Ortu</th>
                    <th>Matematika</th>
                    <th>Inggris</th>
                    <th>Umum</th>
                    <th>Rata-rata</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_registrants === 0): ?>
                    <tr><td colspan="12">Belum ada data pendaftar.</td></tr>
                <?php else: ?>
                    <?php foreach ($registrants as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $row['kode'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['jenis_kelamin'] ?></td>
                            <td><?= $row['ttl'] ?></td>
                            <td><?= $row['asal_sekolah'] ?></td>
                            <td><?= $row['pekerjaan_ortu'] ?></td>
                            <td><?= $row['matematika'] ?></td>
                            <td><?= $row['bahasa_inggris'] ?></td>
                            <td><?= $row['umum'] ?></td>
                            <td><?= $row['average'] ?></td>
                            <?php
                                $statusClass = 'status-tidak-lulus';
                                if ($row['status'] === 'Lulus') {
                                    $statusClass = 'status-lulus';
                                } elseif ($row['status'] === 'Cadangan') {
                                    $statusClass = 'status-cadangan';
                                }
                            ?>
                            <td><span class="status-pill <?= $statusClass ?>"><?= $row['status'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($total_registrants > 0): ?>
            <div class="box" style="margin-top: 16px;">
                <h3>Rata-rata Nilai Keseluruhan</h3>
                <p>Matematika: <strong><?= $average_scores['matematika'] ?></strong></p>
                <p>Bahasa Inggris: <strong><?= $average_scores['bahasa_inggris'] ?></strong></p>
                <p>Umum: <strong><?= $average_scores['umum'] ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
