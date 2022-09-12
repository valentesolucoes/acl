<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaTransacoes extends Migration
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
				'charge_id'          => [ 
						'type'           => 'VARCHAR',
						'constraint'     => '200',
						'null'       => true,
				],
				'barcode'          => [ 
						'type'           => 'VARCHAR',
						'constraint'     => '200',
						'null'       => true,
				],
				'link'          => [ 
						'type'           => 'VARCHAR',
						'constraint'     => '240',
						'null'       => true,
				],
				'pdf'          => [ 
						'type'           => 'VARCHAR',
						'constraint'     => '240',
						'null'       => true,
				],
				'expire_at'       => [
					'type'       => 'DATE',
					'null' => true,
					'default' => null,
				],
				'status'       => [
					'type'       => 'VARCHAR',
					'constraint'     => '128',
					'null' => true,
				],
				'total'       => [
					'type'       => 'DECIMAL',
					'constraint'     => '10,2',
					'null' => true,
				],
		
			]);


			$this->forge->addKey('id', true);

			$this->forge->addForeignKey('ordem_id', 'ordens', 'id', 'CASCADE', 'CASCADE');

			$this->forge->createTable('transacoes');
	}

	public function down()
	{
		$this->forge->dropTable('transacoes');
	}
}
