# DOKUMENTASI PENGERJAAN UAS - PROJECT TOKO CI4

Dokumen ini merangkum seluruh pekerjaan, _logic_, dan keputusan desain yang diimplementasikan pada 6 tugas utama UAS Pemrograman Web Lanjut terkait fitur **Manajemen Diskon dan Pembelian**.

---

## Soal 1: Migration & Seeder table Discount

### 1. Ringkasan Soal
Membangun struktur tabel database `discount` yang memuat tanggal dan nominal diskon, didukung dengan sistem _soft-delete_, serta membuat _Seeder_ untuk menyuntikkan 10 baris data dengan urutan tanggal yang tidak boleh duplikat.

### 2. File yang Dibuat/Diubah
- `app/Database/Migrations/[timestamp]_Discount.php` (Dibuat): Berisi instruksi pembuatan skema tabel (kolom id, tanggal, nominal, created_at, updated_at, deleted_at).
- `app/Database/Seeds/DiscountSeeder.php` (Dibuat): Berisi _script_ untuk meng-generate 10 baris data dummy ke dalam tabel.

### 3. Penjelasan Logic
Fokus utamanya adalah memastikan tipe data kolom sesuai (khususnya `deleted_at` yang di-*set* *nullable* agar fitur *soft-delete* CodeIgniter berjalan normal). Untuk *seeder*, logic utamanya menggunakan _looping_ di mana hari ditambahkan `+1 day` di setiap iterasinya menggunakan objek `Time` (waktu). Hal ini memastikan syarat "tanggal unik dan berurutan dari hari ini" dapat terpenuhi.

### 4. Cuplikan Kode Kunci
```php
// Di dalam DiscountSeeder.php
$data = [];
$today = Time::now('Asia/Jakarta');

for ($i = 0; $i < 10; $i++) {
    $data[] = [
        // Format tanggal hari ini ditambah $i hari (H, H+1, dst)
        'tanggal' => $today->addDays($i)->format('Y-m-d'), 
        // Acak angka nominal diskon antara 100rb - 300rb
        'nominal' => rand(100, 300) * 1000, 
        'created_at' => Time::now(),
        'updated_at' => Time::now(),
    ];
}
// Eksekusi insert banyak data sekaligus
$this->db->table('discount')->insertBatch($data);
```

### 5. Cara Kerja End-to-End
Saat _developer_ menjalankan perintah `php spark migrate` dan `php spark db:seed DiscountSeeder`, sistem otomatis membangun tabel `discount` di database MySQL. Kemudian, tabel akan diisi 10 baris data yang siap diuji di halaman web untuk 10 hari ke depan.

---

## Soal 2: Menampilkan info diskon di Header dan penyesuaian harga di halaman Home

### 1. Ringkasan Soal
Memunculkan *badge* pemberitahuan di Header secara global jika hari ini terdapat diskon, dan menampilkan perbandingan harga (harga coret vs harga diskon) di halaman daftar produk.

### 2. File yang Dibuat/Diubah
- `app/Controllers/BaseController.php` (Diubah): Menambahkan *query* penarikan data diskon harian secara global.
- `app/Views/components/header.php` (Diubah): Menambahkan elemen UI *badge* jika diskon aktif.
- `app/Views/v_home.php` (Diubah): Mengatur logika *render* harga dengan tag `<s>` (strikethrough).

### 3. Penjelasan Logic
Alih-alih melakukan *query* diskon di setiap controller yang membutuhkan, saya meletakkan logic query di dalam `BaseController::initController()`. Hasil query disimpan ke dalam **Session**. Keputusan ini diambil agar ketersediaan nilai diskon bersifat *global* (bisa diakses oleh _header_ di semua halaman web tanpa harus melempar variabel terus menerus) dan juga efisien di sisi *resource* karena tidak perlu hit _database_ berkali-kali.

### 4. Cuplikan Kode Kunci
```php
// Di dalam BaseController.php (initController)
// Mencari diskon yang tanggalnya persis sama dengan tanggal hari ini
$discountModel = new \App\Models\DiscountModel();
$todayDiscount = $discountModel->where('tanggal', date('Y-m-d'))->first();

if ($todayDiscount) {
    // Menyimpan nilai nominal ke session untuk diakses di View mana saja
    session()->set('diskon', $todayDiscount['nominal']);
} else {
    session()->set('diskon', 0);
}
```

### 5. Cara Kerja End-to-End
Sistem secara *default* akan selalu mengecek tanggal *server* dan membandingkannya dengan database diskon. Saat *user* biasa mengakses situs, apabila hari ini kebetulan ada diskon, _Header_ seketika menampilkan *badge* kuning berisi teks nominal. Di halaman utama, user melihat harga asli dicoret tebal dan digantikan dengan harga yang lebih murah di bawahnya.

---

## Soal 3: CRUD menu Diskon khusus admin

### 1. Ringkasan Soal
Membuat modul manajemen diskon eksklusif untuk Admin dengan validasi penambahan yang ketat, dan pelarangan penggantian data tanggal saat pengeditan.

### 2. File yang Dibuat/Diubah
- `app/Filters/AdminRole.php` (Dibuat): Middleware baru untuk menyeleksi *request*.
- `app/Config/Filters.php` & `Routes.php` (Diubah): Mendaftarkan dan mengunci grup *route* dengan filter.
- `app/Controllers/DiscountController.php` & `DiscountModel.php` (Dibuat): Logic manipulasi data diskon.
- `app/Views/diskon/index.php`, `modal_add.php`, `modal_edit.php` (Dibuat): GUI untuk manajemen.
- `app/Views/components/sidebar.php` (Diubah): *Link* navigasi baru (tersembunyi untuk *non-admin*).

### 3. Penjelasan Logic
Saya memutuskan untuk membuat **Filter khusus** bernama `AdminRole` untuk mengamankan halaman. Sebelumnya, project hanya mengandalkan pengecekan "Sudah login", yang artinya user biasa bisa memaksa masuk lewat URL. 
Pada sisi validasi, `is_unique` dipakai di fungsi *Create* untuk mencegah dua diskon di hari yang sama. Pada fungsi *Edit*, elemen UI tanggal saya *set readonly*, namun untuk jaminan 100% aman, di bagian Controller saya meng-`unset()` properti tanggal supaya manipulasi DOM nakal tidak akan mampu mengganti tanggal tersebut.

### 4. Cuplikan Kode Kunci
```php
// Di DiscountController.php (create & update)
// Validasi ketat saat penambahan
$rules = [
    'tanggal' => 'required|is_unique[discount.tanggal]',
    'nominal' => 'required|numeric'
];

// Saat pengeditan, buang tanggal dari body request
// Walau hacker mengubah HTML readonly, sistem tetap menolaknya
$data = $this->request->getPost();
if (isset($data['tanggal'])) {
    unset($data['tanggal']);
}
```

### 5. Cara Kerja End-to-End
Admin _login_, melihat menu "Diskon" di _sidebar_, lalu mengakses tabel berisi rentetan diskon yang aktif maupun terlewat. Saat menekan "Tambah", Admin memilih tanggal. Jika tanggal tersebut sudah dipakai, sistem akan menolak dengan _alert_ error. Saat Admin menekan "Ubah", ia hanya bisa menggeser-geser angka nominal saja. Data yang dihapus akan "menghilang" namun tetap ada di database karena *Soft Delete*.

---

## Soal 4: Penyesuaian harga di Keranjang dan Checkout

### 1. Ringkasan Soal
Menghitung tagihan produk secara otomatis menggunakan "harga yang sudah didiskon" jika sedang ada promo, sambil merender tampilan harga coret di halaman Keranjang & Checkout, serta memastikan database mencatat angka harga yang sesungguhnya dibayarkan.

### 2. File yang Dibuat/Diubah
- `app/Controllers/TransaksiController.php` (Diubah): Penambahan logic diskon sebelum barang masuk *Cart*.
- `app/Views/v_keranjang.php` & `v_checkout.php` (Diubah): Menambahkan elemen *strikethrough* harga.

### 3. Penjelasan Logic
Saya **tidak** mengubah *Core Module* Cart bawaan CI4 karena itu berisiko, melainkan memanipulasi *Input* dan *Data Options*. 
Saat produk di-*add-to-cart*, aplikasi secara _on-the-fly_ memotong nilai `price` dengan diskon, lalu mengemas `harga_asli` ke dalam array *Options*. 
Agar perhitungan tidak kebobolan *bug double-discount* (pengurangan berulang saat halaman direfresh), saya membuat _helper_ internal bernama `_updateCartPrices()` yang dipanggil saat rendering. Ia selalu mengalkulasi ulang dari nilai `harga_asli` mutlak, menjamin total tagihan senantiasa selaras tanpa error *looping*.

### 4. Cuplikan Kode Kunci
```php
// Di dalam TransaksiController.php
private function _updateCartPrices()
{
    $diskon = session()->get('diskon') > 0 ? session()->get('diskon') : 0;
    foreach ($this->cart->contents() as $item) {
        
        // Base perhitungan adalah HARGA ASLI MUTLAK (dari options)
        $harga_asli = $item['options']['harga_asli'] ?? $item['price'];
        $harga_baru = max(0, $harga_asli - $diskon);

        // Update library cart hanya jika terdapat selisih
        if ($item['price'] != $harga_baru) {
            $this->cart->update([
                'rowid' => $item['rowid'],
                'price' => $harga_baru,
            ]);
        }
    }
}
```

### 5. Cara Kerja End-to-End
Saat *User* membungkus produk ke dalam keranjang, mereka akan melihat harga normal (dicoret) dengan harga spesial hijau tebal di sebelahnya. Subtotal perkalian *qty* * harga secara cerdas langsung menggunakan harga diskon, begitupula dengan kalkulasi Total dan tagihan ongkos kirim. Saat tombol pesanan diklik, database (Tabel Transaksi Detail) merekam secara spesifik bahwa transaksi ini dipotong oleh diskon, menyegel harga final sebagai omzet sah.

---

## Soal 5: CRUD menu Pembelian khusus admin

### 1. Ringkasan Soal
Membangun dasbor *super-admin* di mana mereka dapat me-monitoring dan mengubah status seluruh pesanan dari seluruh pembeli lintas *user*.

### 2. File yang Dibuat/Diubah
- `app/Controllers/PembelianController.php` (Dibuat): Menarik semua data *Transaction*.
- `app/Views/pembelian/index.php` (Dibuat): Menampilkan tabel, pop-up modal item yang diorder, dan tombol aksi.
- `app/Config/Routes.php` & `sidebar.php` (Diubah): Registrasi menu & *endpoint*.

### 3. Penjelasan Logic
Logic query yang digunakan sengaja **tidak menggunakan validasi (Where) Username**, berbeda 180 derajat dengan logic pada laman `History` pengguna konvensional. Fitur kunci di bagian ini adalah merubah aksi *"Ubah Status"* menjadi sebuah **POST Form** lengkap dengan atribut `csrf_field()`. 
Keputusan merubah dari model anchor link `<a>` (GET) menjadi POST bertujuan untuk melenyapkan kerentanan CSRF (*Cross-Site Request Forgery*) dimana bot pencarian, atau klik tak disengaja, tidak akan berisiko merubah status omzet (*mutating action*).

### 4. Cuplikan Kode Kunci
```html
<!-- Di dalam v_pembelian/index.php -->
<!-- Tombol mutasi diubah dari <a> menjadi <form> demi alasan keamanan -->
<form action="<?= base_url('pembelian/status/' . $item['id']) ?>" method="POST" class="d-inline">
    <?= csrf_field() ?> <!-- Perlindungan anti-forgery -->
    <button type="submit" class="btn btn-primary btn-sm">Ubah Status</button>
</form>
```

### 5. Cara Kerja End-to-End
Admin melihat daftar pesanan masif. Di kolom *status*, ia bisa mengidentifikasi pesanan mana yang belum selesai dikemas (Tanda Kuning). Ia mengklik "Detail" untuk membuka daftar barang, lalu menyelesaikannya dengan memencet "Ubah Status", di mana indikator langsung berubah hijau menandakan pesanan rampung.

---

## Soal 6: RESTful API untuk data Discount

### 1. Ringkasan Soal
Menyediakan *endpoint* Web Service berarsitektur REST API dengan fungsi penuh CRUD bagi modul Diskon yang dikunci perlindungan otoritas via Bearer Token.

### 2. File yang Dibuat/Diubah
- `app/Controllers/Api/DiscountController.php` (Dibuat): Mengekstensi fungsionalitas `ResourceController`.
- `tests/api/discount.rest` (Dibuat): Berkas injeksi perantara HTTP/REST _Client_.
- `app/Config/Routes.php` (Diubah): Mendaftarkan resource.

### 3. Penjelasan Logic
Saya menjiplak gaya *Response Code*, arsitektur variabel paginasi, serta metode _Parsing Authorization Token_ milik `Api\ProdukController` secara 100% kongruen agar pihak *Frontend/Mobile App* tidak kebingungan.
Untuk menjawab aturan unik form (seperti tanggal dilarang duplikat atau diedit), saya memanfaatkan injeksi validasi `$validation = \Config\Services::validation()` ke *Payload Body* (Create). Dan saat rute *(Update)* ditembak, payload JSON yang berisi elemen `tanggal` akan dieliminasi terlebih dahulu untuk memastikan tanggal abadi, sedangkan elemen `nominal` diteruskan ke _database_.

### 4. Cuplikan Kode Kunci
```php
// Di dalam Api/DiscountController.php (method create)
// Contoh penerapan validasi pada Body JSON REST
$data = $this->request->getJSON(true);
$validation = \Config\Services::validation();

$validation->setRules([
    'tanggal' => 'required|is_unique[discount.tanggal]',
    'nominal' => 'required|numeric'
]);

if (!$validation->run($data)) {
    return $this->failValidationErrors($validation->getErrors());
}
$this->model->insert($data);
```

### 5. Cara Kerja End-to-End
Pihak eksternal (seperti App Android) melempar permintaan `GET /api/discounts` disertakan *Header: Authorization Bearer [Token]*. CI4 memvalidasi apakah token cocok, lalu melayani *response* berformat JSON indah yang menyertakan informasi paginasi. Ketika *Mobile App* mencoba meretas sistem dengan mengirim `PUT` penggantian tanggal, server merespons "Berhasil diupdate" tetapi sesungguhnya sistem secara aman melindungi kolom tanggal dengan mengabaikannya.
