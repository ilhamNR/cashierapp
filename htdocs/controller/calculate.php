<?php

// Definisikan denominasi koin dan uang kertas
$coinDenominations = [100, 200, 500];
$noteDenominations = [1000, 2000, 5000, 10000, 20000, 50000, 100000];

// Fungsi untuk menemukan kombinasi
function findCombinations($amount, $denominations, $partial, $depthLimit) {
    $sum = array_sum($partial);

    // Periksa apakah jumlah sementara sama dengan target
    if ($sum == $amount) {
        return [implode(" + ", $partial)];
    }

    // Jika jumlah sementara lebih besar dari jumlah target atau batas kedalaman tercapai, hentikan eksplorasi jalur ini
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

// Fungsi untuk mendapatkan denominasi yang sesuai berdasarkan jumlah
function getDenominations($amount) {
    global $coinDenominations, $noteDenominations;

    // Jika jumlahnya tepat dibagi oleh denominasi uang kertas manapun, gunakan hanya uang kertas
    foreach ($noteDenominations as $note) {
        if ($amount % $note == 0) {
            return $noteDenominations;
        }
    }

    // Jika tidak, gunakan uang kertas terlebih dahulu, kemudian koin untuk sisa pembayaran
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

// Fungsi utama untuk menangani permintaan
function getCombinations($amount) {
    $results = [];

    // Batasi kedalaman pencarian maksimal menjadi 3
    $depthLimit = 3;

    // Dapatkan denominasi yang sesuai berdasarkan jumlah
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
                $results[] = $paymentAmount;
            }
        }
    }

    // Hapus nilai duplikat dan urutkan opsi pembayaran
    $uniquePaymentOptions = array_unique($results);
    sort($uniquePaymentOptions);

    return $uniquePaymentOptions;
}

// Periksa apakah permintaan adalah permintaan POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
    $billAmount = intval($_POST['amount']);

    // Validasi input
    if ($billAmount > 0) {
        $output = [
            'total_amount' => $billAmount,
            'payment_options' => getCombinations($billAmount)
        ];

        // Output JSON
        // header('Content-Type: application/json');
        echo json_encode($output, JSON_PRETTY_PRINT);
    } else {
        echo "Silakan masukkan jumlah yang valid.";
    }
} else {
    echo "Permintaan tidak valid.";
}

?>
