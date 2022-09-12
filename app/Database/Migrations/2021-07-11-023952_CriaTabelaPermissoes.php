<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaPermissoes extends Migration
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
            'nome'       => [
                    'type'       => 'VARCHAR',
                    'constraint' => '128',
            ],
        ]);


        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nome');

        $this->forge->createTable('permissoes');
    }

    public function down()
    {
        $this->forge->dropTable('permissoes');
    }
}
