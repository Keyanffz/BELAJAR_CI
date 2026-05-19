<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToTables extends Migration
{
    public function up()
    {
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at'
            ]
        ];

        // users
        $this->forge->addColumn('user', $fields);

        // products
        $this->forge->addColumn('product', $fields);

        // transactions
        $this->forge->addColumn('transaction', $fields);

        // transaction_details
        $this->forge->addColumn('transaction_detail', $fields);
    }

    public function down()
    {
        // users
        $this->forge->dropColumn('user', 'deleted_at');

        // products
        $this->forge->dropColumn('product', 'deleted_at');

        // transactions
        $this->forge->dropColumn('transaction', 'deleted_at');

        // transaction_details
        $this->forge->dropColumn('transaction_detail', 'deleted_at');
    }
}