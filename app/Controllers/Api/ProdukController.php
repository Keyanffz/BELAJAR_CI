<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ProductModel;

class ProdukController extends ResourceController
{
    protected $modelName = 'App\Models\ProductModel';
    protected $format    = 'json';
    protected $model;
    private $token;

    function __construct()
    {
        $this->model = new ProductModel();
        $this->token = env('MY_API_KEY');
    }

    private function authenticate()
    {
        $header = $this->request->getHeaderLine('Authorization');

        if (empty($header)) {
            return false;
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return false;
        }

        return $matches[1] === $this->token;
    }

    private function unauthorized()
    {
        return $this->respond([
            'status'  => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    public function index()
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $products = $this->model->paginate($perPage, 'default', $page);

        return $this->respond([
            'data' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page'      => $perPage,
                'last_page'     => $this->model->pager->getPageCount(),
                'total_data'    => $this->model->pager->getTotal(),
                'has_next'      => $page < $this->model->pager->getPageCount(),
                'has_prev'      => $page > 1,
            ]
        ]);
    }

    public function show($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $product = $this->model->find($id);

        if (!$product) {
            return $this->failNotFound('Produk tidak ditemukan');
        }

        return $this->respond($product);
    }

    public function new()
    {
        //
    }

    public function create()
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $data = $this->request->getJSON(true);

        $this->model->insert($data);

        return $this->respondCreated([
            'message' => 'Produk berhasil ditambahkan'
        ]);
    }

    public function edit($id = null)
    {
        //
    }

    public function update($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        if (!$this->model->find($id)) {
            return $this->failNotFound('Produk tidak ditemukan');
        }

        $data = $this->request->getJSON(true);

        $this->model->update($id, $data);

        return $this->respond([
            'message' => 'Produk berhasil diperbarui'
        ]);
    }

    public function delete($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        if (!$this->model->find($id)) {
            return $this->failNotFound('Produk tidak ditemukan');
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
