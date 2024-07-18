<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilihan Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">
    <div class="bg-blue-700 text-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4">Masukkan Jumlah Belanja</h2>
        <form id="amountForm">
            <input type="text" id="amountInput" class="w-full mb-6 p-2 text-gray-800" placeholder="Masukkan jumlah belanja (hanya angka)">
            <p id="amountError" class="text-red-500"></p>
        </form>

        <h3 class="text-xl mb-4">Kemungkinan Pembayaran</h3>
        <div id="paymentOptionsGrid" class="grid grid-cols-3 gap-4 mb-6">
            <!-- Opsi pembayaran akan ditambahkan secara dinamis di sini -->
        </div>

        <button id="calculateButton" class="bg-yellow-500 text-black py-2 px-4 rounded w-full">Kalkulasi</button>
    </div>

    <script>
        // Fungsi untuk mengambil opsi pembayaran dan memperbarui UI
        async function fetchPaymentOptions(amount) {
            try {
                const response = await fetch('controller/calculate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `amount=${amount}`
                });

                if (!response.ok) {
                    throw new Error('Respon jaringan tidak berhasil');
                }

                const data = await response.json();
                return data.payment_options;
            } catch (error) {
                console.error('Error mengambil opsi pembayaran:', error);
                return [];
            }
        }

        // Fungsi untuk memperbarui UI dengan opsi pembayaran
        async function updatePaymentOptions(amount) {
            const paymentOptions = await fetchPaymentOptions(amount);
            const gridContainer = document.getElementById('paymentOptionsGrid');
            gridContainer.innerHTML = ''; // Menghapus konten sebelumnya

            paymentOptions.forEach(option => {
                const button = document.createElement('button');
                button.className = 'bg-gray-200 text-black py-2 px-4 rounded';
                button.textContent = option.toLocaleString('id-ID'); // Format sebagai mata uang Indonesia
                gridContainer.appendChild(button);
            });

            // Menambahkan tombol "UANG PAS"
            const uangPasButton = document.createElement('button');
            uangPasButton.className = 'bg-yellow-500 text-black py-2 px-4 rounded';
            uangPasButton.textContent = 'UANG PAS';
            gridContainer.appendChild(uangPasButton);
        }

        // Fungsi untuk validasi input jumlah
        function validateAmountInput(input) {
            const amountRegex = /^\d+$/;
            return amountRegex.test(input);
        }

        // Event listener untuk tombol "Kalkulasi"
        const calculateButton = document.getElementById('calculateButton');
        calculateButton.addEventListener('click', function() {
            const amountInput = document.getElementById('amountInput').value.trim();

            // Validasi input jumlah
            if (amountInput === '' || !validateAmountInput(amountInput)) {
                const amountError = document.getElementById('amountError');
                amountError.textContent = 'Masukkan jumlah yang valid (hanya angka)';
                return;
            }

            // Menghapus pesan kesalahan sebelumnya jika ada
            document.getElementById('amountError').textContent = '';

            // Memperbarui opsi pembayaran
            updatePaymentOptions(amountInput);
        });

        // Mencegah pengiriman formulir saat tombol Enter ditekan
        const amountForm = document.getElementById('amountForm');
        amountForm.addEventListener('submit', function(event) {
            event.preventDefault();
        });
    </script>
</body>
</html>
