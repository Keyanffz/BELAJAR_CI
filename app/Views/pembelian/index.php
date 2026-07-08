<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Daftar Pembelian (Seluruh User)</h5>
            
            <?php if (session()->getFlashData('success')): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= session()->getFlashData('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <!-- Table with stripped rows -->
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID Pembelian</th>
                            <th scope="col">Pembeli</th>
                            <th scope="col">Waktu Pembelian</th>
                            <th scope="col">Total Bayar</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transactions)) : ?>
                            <?php foreach ($transactions as $index => $item) : ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= $item['username'] ?></td>
                                    <td><?= $item['created_at'] ?></td>
                                    <td><?= number_to_currency($item['total_harga'], 'IDR') ?></td>
                                    <td><?= $item['alamat'] ?></td>
                                    <td>
                                        <?= ($item['status'] == "1")
                                            ? '<span class="badge bg-success">Sudah Selesai</span>'
                                            : '<span class="badge bg-warning text-dark">Belum Selesai</span>' ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailModal-<?= $item['id'] ?>">
                                            Detail
                                        </button>
                                        <form action="<?= base_url('pembelian/status/' . $item['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary btn-sm">Ubah Status</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <!-- End Table with stripped rows -->
            </div>
        </div>
    </div>
</div>

<?php if (!empty($transactions)) : ?>
    <?php foreach ($transactions as $item) : ?>
        <!-- Detail Modal Begin -->
        <div class="modal fade" id="detailModal-<?= $item['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Transaksi #<?= $item['id'] ?> (<?= $item['username'] ?>)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($products[$item['id']])) : ?>
                            <?php foreach ($products[$item['id']] as $index2 => $item2) : ?>
                                <?= $index2 + 1 . ")" ?>

                                <?php
                                $imagePath = FCPATH . 'img/' . $item2['foto'];
                                if (!empty($item2['foto']) && file_exists($imagePath)) :
                                ?>
                                    <div class="my-2">
                                        <img src="<?= base_url('img/' . $item2['foto']) ?>" width="100" class="img-thumbnail">
                                    </div>
                                <?php endif; ?>

                                <strong><?= $item2['nama'] ?></strong><br>
                                Harga: <?= number_to_currency($item2['harga'], 'IDR') ?>
                                <?php if ($item2['diskon'] > 0): ?>
                                    <span class="text-success">(Diskon: -<?= number_to_currency($item2['diskon'], 'IDR') ?>/pcs)</span>
                                <?php endif; ?>
                                <br>
                                <?= "(" . $item2['jumlah'] . " pcs)" ?><br>
                                Subtotal: <?= number_to_currency($item2['subtotal_harga'], 'IDR') ?>
                                <hr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <strong>Ongkir:</strong> <?= number_to_currency($item['ongkir'], 'IDR') ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Detail Modal End -->
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
