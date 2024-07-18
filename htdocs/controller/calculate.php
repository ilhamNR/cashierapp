<?php

// Definisikan pecahan uang yang tersedia
$coinDenominations = [100, 200, 500];
$noteDenominations = [1000, 2000, 5000, 10000, 20000, 50000, 100000];

// Fungsi untuk menemukan kombinasi
function findCombinations($amount, $denominations, $partial = [], $depthLimit) {
    $sum = array_sum($partial);

    // Periksa apakah jumlah sementara sama dengan target
    if ($sum == $amount) {
        return [implode(" + ", $partial)];
    }

    // Jika jumlah sementara lebih besar dari target atau batas kedalaman tercapai, hentikan penjelajahan jalur ini
    if ($sum > $amount || count($partial) >= $depthLimit) {
        return [];
    }

    $combinations = [];
    foreach ($denominations as $i => $denomination) {
        $remaining = array_slice($denominations, $i);
        $combinations = array_merge($combinations, findCombinations($amount, $remaining, array_merge($partial, [$denomination]), $depthLimit));
    }
    return $combinations;
}

// Fungsi untuk mendapatkan pecahan uang yang sesuai berdasarkan jumlah
function getDenominations($amount) {
    global $coinDenominations, $noteDenominations;

    // Jika jumlahnya tepat dibagi oleh pecahan uang kertas, gunakan hanya uang kertas
    foreach ($noteDenominations as $note) {
        if ($amount % $note == 0) {
            return $noteDenominations;
        }
    }

    // Jika tidak, gunakan uang kertas terlebih dahulu, lalu koin untuk sisanya
    return array_merge($noteDenominations, $coinDenominations);
}

// Fungsi untuk memeriksa apakah ada subset dari kombinasi yang jumlahnya sama dengan jumlah yang dimaksud
function hasSubsetSum($combo, $amount) {
    $n = count($combo);
    for ($i = 1; $i < (1 << $n); $i++) {
        $subsetSum = 0;
        for ($j = 0; $j < $n; $j++) {
            if ($i & (1 << $j)) {
                $subsetSum += $combo[$j];
            }
        }
        if ($subsetSum == $amount) {
            return true;
        }
    }
    return false;
}

// Fungsi untuk memprioritaskan pecahan uang kertas
function prioritizeNotes($amount, $combinations) {
    global $noteDenominations;

    $results = [];
    foreach ($combinations as $paymentAmount => $combos) {
        // Saring kombinasi yang menggunakan lebih dari satu pecahan uang kertas jika satu pecahan uang kertas dapat mencakup jumlahnya
        $filteredCombos = array_filter($combos, function($combo) use ($paymentAmount) {
            global $noteDenominations;
            $comboArray = array_map('intval', explode(" + ", $combo));
            $noteCount = count(array_filter($comboArray, function($value) use ($noteDenominations) {
                return in_array($value, $noteDenominations);
            }));
            return $noteCount <= 1;
        });

        if (!empty($filteredCombos)) {
            $results[$paymentAmount] = $filteredCombos;
        }
    }

    return $results;
}

// Fungsi utama untuk menangani permintaan
function getCombinations($amount) {
    $results = [];

    // Batasi kedalaman pencarian ke maksimal 3
    $depthLimit = 3;

    // Dapatkan pecahan uang yang sesuai berdasarkan jumlah
    $denominations = getDenominations($amount);

    // Hitung kombinasi untuk setiap jumlah pembayaran
    foreach ($denominations as $denomination) {
        $paymentAmount = $amount + $denomination - ($amount % $denomination);
        if ($paymentAmount > $amount) {
            $allCombos = findCombinations($paymentAmount, $denominations, [], $depthLimit);
            // Saring kombinasi yang mengandung subset yang jumlahnya sama dengan jumlah asli
            $filteredCombos = array_filter($allCombos, function($combo) use ($amount) {
                $comboArray = array_map('intval', explode(" + ", $combo));
                return !hasSubsetSum($comboArray, $amount);
            });

            if (!empty($filteredCombos)) {
                $results[$paymentAmount] = $filteredCombos;
            }
        }
    }

    // Prioritaskan pecahan uang kertas
    $results = prioritizeNotes($amount, $results);

    return $results;
}

// Periksa apakah permintaan adalah permintaan POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
    $billAmount = intval($_POST['amount']);

    // Validasi input
    if ($billAmount > 0) {
        echo "Total Belanja Konsumen: Rp " . number_format($billAmount, 0, ',', '.') . ",-<br>";
        echo "Kemungkinan uang yang di bayarkan konsumen adalah:<br>";
        $combinations = getCombinations($billAmount);
        foreach ($combinations as $paymentAmount => $combos) {
            echo "Rp " . number_format($paymentAmount, 0, ',', '.') . "<br>";
            foreach ($combos as $i => $combo) {
                echo ($i + 1) . ". " . $combo . "<br>";
            }
            echo "<br>";
        }
    } else {
        echo "Silakan masukkan jumlah yang valid.";
    }
} else {
    echo "Permintaan tidak valid.";
}

?>
