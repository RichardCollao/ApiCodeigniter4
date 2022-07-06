<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Indicators extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_indicator' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null' => FALSE,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null' => FALSE,
            ],
            'unidad_medida' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null' => FALSE,
            ],
            'fecha' => [
                'type' => 'DATE',
                'null' => FALSE,
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,4',
                'null' => FALSE,
                'default' => 0.00
            ]
        ]);
        $this->forge->addKey('id_indicator', true);
        $this->forge->createTable('indicators');
    }

    public function down()
    {
        $this->forge->dropTable('indicators');
    }
}
