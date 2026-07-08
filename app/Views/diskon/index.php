<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Daftar Diskon</h5>
            
            <?php if (session()->getFlashData('success')): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= session()->getFlashData('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashData('failed')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashData('failed') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Tambah Data
            </button>
            <br><br>

            <!-- Table with stripped rows -->
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Nominal</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($discounts as $key => $diskon) : ?>
                        <tr>
                            <th scope="row"><?= $key + 1 ?></th>
                            <td><?= $diskon['tanggal'] ?></td>
                            <td>Rp <?= number_format($diskon['nominal'], 0, ',', '.') ?></td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editModal-<?= $diskon['id'] ?>">
                                    Ubah
                                </button>
                                <a href="<?= base_url('diskon/delete/' . $diskon['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini ?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <!-- End Table with stripped rows -->

        </div>
    </div>
</div>

<!-- Memanggil Modal Tambah Data -->
<?= $this->include('diskon/modal_add') ?>

<!-- Memanggil Modal Edit Data -->
<?= $this->include('diskon/modal_edit') ?>

<?= $this->endSection() ?>
