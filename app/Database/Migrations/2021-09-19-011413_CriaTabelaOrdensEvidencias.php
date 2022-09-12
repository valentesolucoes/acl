<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaOrdensEvidencias extends Migration
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
				'evidencia'          => [ 
						'type'           => 'VARCHAR',
						'constraint'     => '255',
				],
			]);


			$this->forge->addKey('id', true);

			$this->forge->addForeignKey('ordem_id', 'ordens', 'id', 'CASCADE', 'CASCADE');

			$this->forge->createTable('ordens_evidencias');
	}

	public function down()
	{
		$this->forge->dropTable('ordens_evidencias');
	}
}
