<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FormaPagamentoSeeder extends Seeder
{
	public function run()
	{
		
		$formaPagamentoModel = new \App\Models\FormaPagamentoModel();


		$formas = [

			[
				'nome' => 'Boleto bancário',
				'descricao' => 'Pagamento com boleto bancário gerdo junto à Gerencianet',
				'ativo' => true,
			],
			[
				'nome' => 'Cortesia',
				'descricao' => 'Forma de pagamento destina apenas às ordes que não geraram valor',
				'ativo' => true,
			],
			[
				'nome' => 'Cartão de crédito',
				'descricao' => 'Forma de pagamento com Cartão de crédito. Trabalha com as bandeiras Master, Visa, ELO, etc.',
				'ativo' => true,
			],
			[
				'nome' => 'Cartão de débito',
				'descricao' => 'Forma de pagamento com Cartão de débito. Trabalha com as bandeiras Master, Visa, ELO, etc.',
				'ativo' => true,
			],

		];


		foreach($formas as $forma){

			$formaPagamentoModel->skipValidation(true)->protect(false)->insert($forma);

		}

		echo "Formas de pagamentos criadas com sucesso!";

	}
}
