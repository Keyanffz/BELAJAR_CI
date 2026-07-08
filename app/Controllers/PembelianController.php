<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class PembelianController extends BaseController
{
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
    {
        helper(['number', 'form']);
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
    }

    public function index()
    {
        // Get all transactions for admin (from all users)
        $transactions = $this->transactionModel->orderBy('id', 'DESC')->findAll();
        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

        $data = [
            'transactions' => $transactions,
            'products'     => $products
        ];

        return view('pembelian/index', $data);
    }

    public function update_status($id)
    {
        $transaction = $this->transactionModel->find($id);
        if ($transaction) {
            $newStatus = $transaction['status'] == 1 ? 0 : 1;
            $this->transactionModel->update($id, ['status' => $newStatus]);
            session()->setFlashdata('success', 'Status pesanan berhasil diubah.');
        }

        return redirect()->to('pembelian');
    }
}
