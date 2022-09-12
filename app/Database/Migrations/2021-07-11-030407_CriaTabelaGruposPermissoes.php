<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaGruposPermissoes extends Migration
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
            'grupo_id'          => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
            ],
            'permissao_id'          => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
            ],
        ]);


        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permissao_id', 'permissoes', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('grupos_permissoes');
    }

    public function down()
    {
        $this->forge->dropTable('grupos_permissoes');
    }
}
