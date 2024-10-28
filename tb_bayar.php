<?php
include 'conn.php';


if (isset($_POST['id_bayar'])) {
    $id_bayar = $_POST['id_bayar'];
    $id_transaksi = $_POST['id_transaksi'];
    $nik = $_POST['nik'];
    $query = $conn->query("SELECT tb_bayar.*, tb_kembali.denda FROM tb_bayar 
                           LEFT JOIN tb_kembali ON tb_bayar.id_kembali = tb_kembali.id_kembali 
                           WHERE tb_bayar.id_bayar = '$id_bayar'");
    $data = $query->fetch_assoc();
}

if (isset($_POST['btnSimpan'])) {
    $nominal_bayar = str_replace(['Rp', '.', ' '], '', $_POST['nominal_bayar']); // Remove formatting
    $total_bayar = $data['total_bayar'];
    $tgl_bayar = date('Y-m-d');
    $status = 'lunas';

    if ($nominal_bayar != $total_bayar) {
        echo "<script>alert('Nominal yang dibayarkan tidak sesuai dengan total pembayaran!');</script>";
    } else {
        $conn->query("UPDATE tb_transaksi SET kekurangan = 0 WHERE id_transaksi = $id_transaksi");

        $stmt = $conn->prepare("UPDATE tb_bayar SET tgl_bayar = ?, total_bayar = ?, status = ? WHERE id_bayar = ?");
        $stmt->bind_param('sssi', $tgl_bayar, $nominal_bayar, $status, $id_bayar);

        if ($stmt->execute()) {
            echo "<script>alert('Pembayaran berhasil!'); window.location.href='tb_bayar.php';</script>";
        } else {
            echo "<script>alert('Pembayaran gagal!');</script>";
        }

        $stmt->close();
    }
}
?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <title>USER</title>
</head>

<body class="bg-gray-200">
    <?php include 'navbar.php'; ?>
    <div class="p-4 sm:ml-64 mt-14">
        <h1 class="text-center  text-2xl font-bold mb-4">Pembayaran Mobil</h1>

        <div class="grid grid-flow-col justify-start gap-x-2 mb-3">
            <a href="tb_booking.php"
                class="text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-emerald-600 dark:hover:bg-emerald-700 focus:outline-none dark:focus:ring-emerald-800">
                Kembali</a>
        </div>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-center">No</th>
                        <th scope="col" class="px-6 py-3 text-center">NIK</th>
                        <th scope="col" class="px-6 py-3 text-center">No. Polisi</th>
                        <th scope="col" class="px-6 py-3 text-center">Tanggal Bayar</th>
                        <th scope="col" class="px-6 py-3 text-center">Total Pembayaran</th>
                        <th scope="col" class="px-6 py-3 text-center">Status Pembayaran</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT tb_bayar.id_bayar, tb_bayar.id_kembali, tb_bayar.tgl_bayar, tb_bayar.total_bayar, tb_bayar.status, tb_transaksi.nik, tb_transaksi.nopol, tb_transaksi.id_transaksi
                              FROM tb_bayar 
                              JOIN tb_kembali ON tb_bayar.id_kembali = tb_kembali.id_kembali
                              JOIN tb_transaksi ON tb_kembali.id_transaksi = tb_transaksi.id_transaksi";
                    $result = mysqli_query($conn, $query);
                    $no = 1;

                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600'>
                            <td class='px-6 py-4 whitespace-nowrap text-center'><?= $no++ ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-center'><?= $row['nik'] ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-center'><?= $row['nopol'] ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-center'><?= $row['tgl_bayar'] ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-center'>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-center'><?= $row['status'] ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-center'>
                            <?php
                                    if($row['status'] != 'lunas') :
                                ?>
                                <button type="button" 
                                    data-id="<?= $row['id_bayar'] ?>" 
                                    data-nik="<?= $row['nik'] ?>" 
                                    data-transaksi="<?= $row['id_transaksi'] ?>" 
                                    data-total="<?= number_format($row['total_bayar'], 0, ',', '.') ?>" 

                                    class="open-modal text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2 dark:bg-emerald-600 dark:hover:bg-emerald-700 focus:outline-none dark:focus:ring-emerald-800">Bayar</button>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center h-screen">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-bold mb-4">Pembayaran</h2>
                <form method="POST" id="paymentForm">
                    <input type="hidden" name="id_bayar" id="modalIdBayar">
                    <input type="hidden" name="id_transaksi" id="modalIdTransaksi">
                    <input type="hidden" name="nik" id="modalNIK">
                    
                    <div class="mb-4">
                        <label for="nominal_bayar" class="block mb-2 text-sm font-medium text-gray-900">Nominal Bayar</label>
                        <input type="text" name="nominal_bayar" id="nominal_bayar" 
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required oninput="formatRupiah(this)">
                    </div>
                    
                    <button type="submit" name="btnSimpan"
                        class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Bayar</button>
                </form>
                <button class="mt-4 text-red-500" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.open-modal').forEach(button => {
            button.addEventListener('click', function() {
                const idBayar = this.getAttribute('data-id');
                const nik = this.getAttribute('data-nik');
                const IdTransaksi = this.getAttribute('data-transaksi');
                const total = this.getAttribute('data-total');
                document.getElementById('modalIdBayar').value = idBayar;
                document.getElementById('nominal_bayar').value = total;
                document.getElementById('modalIdTransaksi').value = IdTransaksi;
                document.getElementById('modalNIK').value = nik;
                document.getElementById('paymentModal').classList.remove('hidden');
                formatRupiah(document.getElementById('nominal_bayar'));
            });
        });

        function closeModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            input.value = 'Rp ' + rupiah;
        }
    </script>
</body>

</html>
