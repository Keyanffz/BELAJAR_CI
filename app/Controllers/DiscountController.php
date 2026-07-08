<?php

namespace App\Controllers;

use App\Models\DiscountModel;

class DiscountController extends BaseController
{
    protected $discountModel;

    public function __construct()
    {
        helper('form');
        $this->discountModel = new DiscountModel();
    }

    public function index()
    {
        return view('diskon/index', [
            'discounts' => $this->discountModel->findAll()
        ]);
    }

    public function create()
    {
        $rules = [
            'tanggal' => [
                'rules' => 'required|is_unique[discount.tanggal]',
                'errors' => [
                    'is_unique' => 'The tanggal field must contain a unique value.'
                ]
            ],
            'nominal' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('failed', $this->validator->getError('tanggal') ?: 'Gagal menambahkan data. Pastikan input valid.');
        }

        $this->discountModel->insert([
            'tanggal' => $this->request->getPost('tanggal'),
            'nominal' => $this->request->getPost('nominal')
        ]);

        return redirect()->to('diskon')->with('success', 'Data Berhasil Ditambah');
    }

    public function edit($id)
    {
        // Tanggal is read-only in view, we only update nominal
        $this->discountModel->update($id, [
            'nominal' => $this->request->getPost('nominal')
        ]);

        return redirect()->to('diskon')->with('success', 'Data Berhasil Diubah');
    }

    public function delete($id)
    {
        $this->discountModel->delete($id);

        return redirect()->to('diskon')->with('success', 'Data Berhasil Dihapus');
    }
}
