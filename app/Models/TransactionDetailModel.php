<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailModel extends Model
{
    protected $table            = 'transaction_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['transaction_id', 'product_id', 'jumlah', 'diskon', 'subtotal_harga'];
    protected $useTimestamps    = true;
    protected $skipValidation   = true;

    public function getProductsByTransactionIds(array $transactionIds)
    {
        if (empty($transactionIds)) {
            return [];
        }

        $details = $this->select('transaction_detail.*, product.nama, product.harga, product.foto')
            ->join('product', 'transaction_detail.product_id = product.id')
            ->whereIn('transaction_id', $transactionIds)
            ->findAll();

        $products = [];

        foreach ($details as $detail) {
            $products[$detail['transaction_id']][] = $detail;
        }

        return $products;
    }
}
