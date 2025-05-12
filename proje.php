<?php

// Dosyaları oku
$dersSatirlari = file('dersler.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$derslikSatirlari = file('derslikler.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$dersler = [];
$derslikler = [];
$zamanCetveli = [];

$gunler = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma'];
$saatler = [
    "09:00-09:50", "10:00-10:50", "11:00-11:50",
    "12:00-12:50", "13:00-13:50", "14:00-14:50",
    "15:00-15:50", "16:00-16:50", "17:00-17:50"
];

// Slot numarasını gün ve saat olarak biçimlendir
function slotFormatla($slot) {
    global $gunler, $saatler;
    $gunIndex = floor(($slot - 1) / 9);
    $saatIndex = ($slot - 1) % 9;
    return $gunler[$gunIndex] . ' ' . $saatler[$saatIndex];
}

// Belirli bir derslikte saat çakışması var mı kontrol et
function cakismaVarMi($derslik, $slotlar) {
    global $zamanCetveli;
    foreach ($slotlar as $slot) {
        if (!empty($zamanCetveli[$derslik][$slot])) {
            return true;
        }
    }
    return false;
}

// Derslikleri hazırla
foreach ($derslikSatirlari as $satir) {
    list($ad, $kapasite, $tur, $projeksiyon) = explode(" ", trim($satir));
    $derslikler[] = [
        'ad' => $ad,
        'kapasite' => (int)$kapasite,
        'tur' => (int)$tur,
        'projeksiyon' => (int)$projeksiyon
    ];
    $zamanCetveli[$ad] = array_fill(1, 45, null);
}

// Dersleri hazırla
foreach ($dersSatirlari as $satir) {
    $veri = explode(" ", trim($satir));
    if (count($veri) < 12) continue;

    $dersler[] = [
        'kod' => $veri[0],
        'saat' => (int)$veri[1],
        'projeksiyon' => (int)$veri[2],
        'tur' => (int)$veri[3],
        'mevcud' => (int)$veri[4],
        'saatler' => array_slice($veri, 5, 6),
        'ozelDerslik' => end($veri)
    ];
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ders Programı</title>
    <style>
        body { font-family: Arial; background: #f4f4f9; margin: 20px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eaeaea; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .container { width: 90%; margin: auto; }
    </style>
</head>
<body>
<div class="container">

<h1>Derslerin Gün ve Derslik Bilgileri</h1>
<table>
    <tr><th>Ders Kodu</th><th>Gün ve Saat</th><th>Derslik</th></tr>
    <?php foreach ($dersler as $ders): ?>
        <tr>
            <td><?= $ders['kod'] ?></td>
            <td>
                <?php
                    $cakisma = cakismaVarMi($ders['ozelDerslik'], $ders['saatler']);
                    if ($cakisma) {
                        echo 'Saat çakışması var';
                    } else {
                        foreach ($ders['saatler'] as $slot) {
                            $zamanCetveli[$ders['ozelDerslik']][$slot] = $ders['kod'];
                            echo slotFormatla($slot) . '<br>';
                        }
                    }
                ?>
            </td>
            <td><?= $ders['ozelDerslik'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h1>Derslik Bazlı Tüm Saatlik Program</h1>
<table>
    <tr>
        <th>Derslik</th>
        <?php for ($i = 1; $i <= 45; $i++): ?>
            <th><?= slotFormatla($i) ?></th>
        <?php endfor; ?>
    </tr>
    <?php foreach ($derslikler as $derslik): ?>
        <tr>
            <td><?= $derslik['ad'] ?></td>
            <?php for ($i = 1; $i <= 45; $i++): ?>
                <td><?= $zamanCetveli[$derslik['ad']][$i] ?? '-' ?></td>
            <?php endfor; ?>
        </tr>
    <?php endforeach; ?>
</table>

<h1>Dersliklerin Haftalık Programı</h1>
<?php foreach ($derslikler as $derslik): ?>
    <h3><?= $derslik['ad'] ?> - Haftalık Program</h3>
    <table>
        <tr>
            <th>Gün / Saat</th>
            <?php for ($i = 0; $i < 9; $i++): ?>
                <th><?= $saatler[$i] ?></th>
            <?php endfor; ?>
        </tr>
        <?php foreach ($gunler as $gunIndex => $gun): ?>
            <tr>
                <td><?= $gun ?></td>
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <?php $slot = $gunIndex * 9 + $i; ?>
                    <td><?= $zamanCetveli[$derslik['ad']][$slot] ?? '-' ?></td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>

</div>
</body>
</html>
