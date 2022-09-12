<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaOrdensItens extends Migration
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
				'ordem_id'          => [
						'type'           => 'INT',
						'constraint'     => 5,
						'unsigned'       => true,
						'null'       => true,
				],
				'item_id'          => [ 
						'type'           => 'INT',
						'constraint'     => 5,
						'unsigned'       => true,
				],
				'item_quantidade'        => [ 
						'type'           => 'INT',
						'constraint'     => 5,
				],
			]);


			$this->forge->addKey('id', true);

			$this->forge->addForeignKey('ordem_id', 'ordens', 'id', 'CASCADE', 'CASCADE');
			$this->forge->addForeignKey('item_id', 'itens', 'id', 'CASCADE', 'CASCADE');
	

			$this->forge->createTable('ordens_itens');
	}

	public function down()
	{
		$this->forge->dropTable('ordens_itens');
	}
}
