<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DiscountModel;

class DiscountController extends ResourceController
{
    protected $modelName = 'App\Models\DiscountModel';
    protected $format    = 'json';
    protected $model;
    private $token;

    function __construct()
    {
        $this->model = new DiscountModel();
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

        $discounts = $this->model->paginate($perPage, 'default', $page);

        return $this->respond([
            'data' => $discounts,
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

        $discount = $this->model->find($id);

        if (!$discount) {
            return $this->failNotFound('Discount tidak ditemukan');
        }

        return $this->respond($discount);
    }

    public function create()
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $data = $this->request->getJSON(true);

        $validation = \Config\Services::validation();
        $validation->setRules([
            'tanggal' => [
                'rules' => 'required|is_unique[discount.tanggal]',
                'errors' => [
                    'is_unique' => 'The tanggal field must contain a unique value.'
                ]
            ],
            'nominal' => 'required|numeric'
        ]);

        if (!$validation->run($data)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $this->model->insert($data);

        return $this->respondCreated([
            'message' => 'Discount berhasil ditambahkan'
        ]);
    }

    public function update($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        if (!$this->model->find($id)) {
            return $this->failNotFound('Discount tidak ditemukan');
        }

        $data = $this->request->getJSON(true);

        // Mencegah perubahan field tanggal sesuai dengan aturan di form web
        if (isset($data['tanggal'])) {
            unset($data['tanggal']);
        }

        // Kalau ada data yang bisa diupdate (selain tanggal)
        if (!empty($data)) {
            $this->model->update($id, $data);
        }

        return $this->respond([
            'message' => 'Discount berhasil diperbarui'
        ]);
    }

    public function delete($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        if (!$this->model->find($id)) {
            return $this->failNotFound('Discount tidak ditemukan');
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'message' => 'Discount berhasil dihapus'
        ]);
    }
}
