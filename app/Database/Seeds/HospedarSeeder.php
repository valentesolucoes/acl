<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HospedarSeeder extends Seeder
{
	public function run()
	{
		$this->call('UsuarioAdminSeeder');
		$this->call('PermissaoSeeder');
		$this->call('FormaPagamentoSeeder');
	}
}
