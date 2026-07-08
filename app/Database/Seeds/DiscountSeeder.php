<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        // membuat data
        $data = [];
        $startDate = date("Y-m-d");

        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'tanggal'    => date('Y-m-d', strtotime("$startDate +$i days")),
                'nominal'    => rand(100000, 300000),
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
        }

        foreach ($data as $item) {
            // insert semua data ke tabel
            $this->db->table('discount')->insert($item);
        }
    }
}
