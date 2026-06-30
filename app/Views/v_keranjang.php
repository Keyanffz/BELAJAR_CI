<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashData('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?= form_open('keranjang/edit') ?>
<table class="table datatable">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Foto</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Subtotal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; ?>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td><?= $item['name'] ?></td>
                <td>
                    <img src="<?= base_url('img/' . $item['options']['foto']) ?>" width="80" alt="foto">
                </td>
                <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                <td>
                    <input type="number" name="qty<?= $i++ ?>" value="<?= $item['qty'] ?>" class="form-control" min="1">
                </td>
                <td><?= number_to_currency($item['subtotal'], 'IDR') ?></td>
                <td>
                    <a href="<?= base_url('keranjang/delete/' . $item['rowid']) ?>" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<button type="submit" class="btn btn-primary">Perbarui Keranjang</button>
<?= form_close() ?>

<br>
<div class="alert alert-info">Total = <?= number_to_currency($total, 'IDR') ?></div>

<a href="<?= base_url('keranjang/clear') ?>" class="btn btn-warning">Kosongkan Keranjang</a>

<?= $this->endSection() ?>