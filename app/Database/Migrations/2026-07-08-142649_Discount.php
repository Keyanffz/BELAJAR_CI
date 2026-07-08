<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Discount extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => FALSE,
            ],
            'nominal' => [
                'type' => 'DOUBLE',
                'null' => FALSE,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
        ]);

        $this->forge->addKey('id', TRUE);
        $this->forge->createTable('discount');
    }

    public function down()
    {
        $this->forge->dropTable('discount');
    }
}
