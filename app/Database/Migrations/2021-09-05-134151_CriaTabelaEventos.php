<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaEventos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
                    'auto_increment' => true,
            ],
            'conta_id'          => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
                    'null'       => true,
            ],
            'title'       => [
                    'type'       => 'VARCHAR',
                    'constraint' => '200',
            ],
            'start'       => [
                'type'       => 'DATETIME',
            ],
            'end'       => [
                'type'       => 'DATETIME',
            ],
            'created_at'       => [
                'type'       => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at'       => [
                'type'       => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);


        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('conta_id', 'contas_pagar', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('eventos');
    }

    public function down()
    {
        $this->forge->dropTable('eventos');
    }
}
