<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaItensImagens extends Migration
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
            'item_id'          => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
            ],
            'imagem'          => [
                    'type'           => 'VARCHAR',
                    'constraint'     => '255',
            ],
        ]);


        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('item_id', 'itens', 'id', 'CASCADE', 'CASCADE');
        

        $this->forge->createTable('itens_imagens');
    }

    public function down()
    {
        $this->forge->dropTable('itens_imagens');
    }
}
