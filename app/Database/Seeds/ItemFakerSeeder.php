<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemFakerSeeder extends Seeder
{
	public function run()
	{
		
		$itemModel = new \App\Models\ItemModel();

		

		// use a fábrica para criar uma instância Faker \ Generator
        $faker = \Faker\Factory::create('pt-BR');

		$faker->addProvider(new \Faker\Provider\pt_BR\Person($faker)); 

		// Para utilizamos com o método geraCodigoInternoItem()
		helper('text');

		$itensPush = [];

		$criarQuantosItens = 5000;


		for($i = 0; $i < $criarQuantosItens; $i++){

			$tipo = $faker->randomElement($array = array('produto', 'serviço'));

			$controlaEstoque = $faker->numberBetween(0, 1); // true ou false


			array_push($itensPush, [

				'codigo_interno' => $itemModel->geraCodigoInternoItem(),
				'nome' => $faker->unique()->words(3, true),
				'marca' => ($tipo === 'produto' ? $faker->word : null),                          //$faker->word, // aqui é singular
				'modelo' => ($tipo === 'produto' ? $faker->unique()->words(2, true) : null),                  //$faker->unique()->words(2, true),
				'preco_custo' => $faker->randomFloat(2, 10, 100), // aqui máximo 100 para ficar menor que o preço de venda
				'preco_venda' => $faker->randomFloat(2, 100, 1000), // aqui 100, 1000 para ficar maior que o preço de custo
				'estoque' => ($tipo === 'produto' ? $faker->randomDigitNot(0) : null),
				'controla_estoque' => ($tipo === 'produto' ? $controlaEstoque : null),
				'tipo' => $tipo,
				'ativo' => $controlaEstoque,
				'descricao' => $faker->text(300),
				'criado_em' => $faker->dateTimeBetween('-2 month', '-1 days')->format('Y-m-d H:i:s'),
				'atualizado_em' => $faker->dateTimeBetween('-2 month', '-1 days')->format('Y-m-d H:i:s'),

			]);

		}


		$itemModel->skipValidation(true)->insertBatch($itensPush);


		echo "$criarQuantosItens semeados com sucesso!";


	}
}
