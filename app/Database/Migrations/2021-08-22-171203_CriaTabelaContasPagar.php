<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaContasPagar extends Migration
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
            'fornecedor_id'          => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
            ],
            'valor_conta'       => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
            ],
            'data_vencimento'       => [
                'type'       => 'DATE',
            ],
            'descricao_conta'       => [
                'type'       => 'TEXT',
            ],
            'situacao'       => [
                'type'       => 'BOOLEAN', // 0 - Em aberto | 1 - Paga
            ],
            'criado_em'       => [
                'type'       => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'atualizado_em'       => [
                'type'       => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);


        $this->forge->addKey('id', true);

		$this->forge->addForeignKey('fornecedor_id', 'fornecedores', 'id');

        $this->forge->createTable('contas_pagar');
    }

    public function down()
    {
        $this->forge->dropTable('contas_pagar');
    }
}
