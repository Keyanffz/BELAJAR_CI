<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Produk</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Data Produk</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Foto</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $key => $produk) : ?>
                <tr>
                    <td><?= $key + 1 ?></td>
                    <td>
                        <?php
                        $path = FCPATH . 'img/' . $produk['foto'];
                        if ($produk['foto'] != '' && file_exists($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            echo '<img src="' . $base64 . '" width="80" alt="Foto">';
                        } else {
                            echo 'Tidak ada gambar';
                        }
                        ?>
                    </td>
                    <td><?= $produk['nama'] ?></td>
                    <td>Rp <?= number_format($produk['harga'], 2, ",", ".") ?></td>
                    <td><?= $produk['jumlah'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <p>Downloaded on <?= date("Y-m-d H:i:s") ?></p>
</body>
</html>
