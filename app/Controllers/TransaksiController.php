<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class TransaksiController extends BaseController
{
    protected $cart;
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
    {
        helper(['number', 'form']);
        $this->cart = service('cart');
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
    }

    private function _updateCartPrices()
    {
        $diskon = session()->get('diskon') > 0 ? session()->get('diskon') : 0;
        foreach ($this->cart->contents() as $item) {
            $harga_asli = $item['options']['harga_asli'] ?? $item['price'];
            $harga_baru = max(0, $harga_asli - $diskon);
            
            $options = $item['options'];
            $options['harga_asli'] = $harga_asli;

            if ($item['price'] != $harga_baru || !isset($item['options']['harga_asli'])) {
                $this->cart->update([
                    'rowid' => $item['rowid'],
                    'price' => $harga_baru,
                    'options' => $options
                ]);
            }
        }
    }

    public function index()
    {
        $this->_updateCartPrices();
        return view('v_keranjang', [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ]);
    }

    public function cart_add()
    {
        $harga_asli = $this->request->getPost('harga');
        $diskon = session()->get('diskon') > 0 ? session()->get('diskon') : 0;
        $harga_baru = max(0, $harga_asli - $diskon);

        $this->cart->insert([
            'id' => $this->request->getPost('id'),
            'qty' => 1,
            'price' => $harga_baru,
            'name' => $this->request->getPost('nama'),
            'options' => [
                'foto' => $this->request->getPost('foto'),
                'harga_asli' => $harga_asli
            ]
        ]);
        session()->setFlashdata('success', 'Produk berhasil ditambahkan ke keranjang');
        return redirect()->to(base_url('/'));
    }

    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $item) {
            $qty = $this->request->getPost('qty' . $i++);
            $this->cart->update([
                'rowid' => $item['rowid'],
                'qty' => $qty
            ]);
        }
        session()->setFlashdata('success', 'Keranjang berhasil diperbarui');
        return redirect()->to(base_url('keranjang'));
    }

    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);
        session()->setFlashdata('success', 'Produk berhasil dihapus dari keranjang');
        return redirect()->to(base_url('keranjang'));
    }

    public function cart_clear()
    {
        $this->cart->destroy();
        session()->setFlashdata('success', 'Keranjang berhasil dikosongkan');
        return redirect()->to(base_url('keranjang'));
    }

    public function checkout()
    { 
        $this->_updateCartPrices();
        $data = [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ];

        return view('v_checkout', $data);
    }

    public function destinations()
    {
        $search = $this->request->getGet('q');

        if (empty($search)) {
            return $this->response->setJSON([
                'results' => []
            ]);
        }

        $service = new RajaOngkirService();
        $response = $service->getDestination($search);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'id'   => $item['id'],
                'text' => $item['label']
            ];
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }

    public function costs()
    {
        $origin = '64999';
        $destination = $this->request->getGet('destination');
        $weight = '1000';
        $courier = 'jne';

        if (empty($destination)) {
            return $this->response->setJSON([]);
        }

        $service = new RajaOngkirService();
        $response = $service->getCost($origin, $destination, $weight, $courier);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'service'     => $item['service'],
                'description' => $item['description'],
                'cost'        => $item['cost'],
                'etd'         => $item['etd']
            ];
        }

        return $this->response->setJSON($results);
    }

    public function buy()
    {
        $this->_updateCartPrices();
        $cartItems = $this->cart->contents();

        if (empty($cartItems)) {
            return redirect()->back();
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        $ongkir = (int) $this->request->getPost('ongkir');

        $transaction = [
            'username'    => $this->request->getPost('username'),
            'alamat'      => $this->request->getPost('alamat'),
            'ongkir'      => $ongkir,
            'total_harga' => $subtotal + $ongkir,
            'status'      => 0,
        ];

        if (!$this->transactionModel->insert($transaction)) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        $transactionId = $this->transactionModel->getInsertID();

        foreach ($cartItems as $item) {
            $diskon_item = isset($item['options']['harga_asli']) ? ($item['options']['harga_asli'] - $item['price']) : 0;
            $this->transactionDetailModel->insert([
                'transaction_id' => $transactionId,
                'product_id'     => $item['id'],
                'jumlah'         => $item['qty'],
                'diskon'         => $diskon_item,
                'subtotal_harga' => $item['qty'] * $item['price']
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        $this->cart->destroy();
        return redirect()->to(base_url());
    }

    public function history()
    {
        $username = session()->get('username');

        $transactions = $this->transactionModel->where('username', $username)->findAll();
        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

        $data = [
            'username'     => $username,
            'transactions' => $transactions,
            'products'     => $products
        ];

        return view('v_history', $data);
    }
}
