<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FornecedorFakerSeeder extends Seeder
{
	public function run()
	{
		
		$fornecedorModel = new \App\Models\FornecedorModel();

		// use a fábrica para criar uma instância Faker \ Generator
        $faker = \Faker\Factory::create('pt-BR');

		$faker->addProvider(new \Faker\Provider\pt_BR\Company($faker)); // Para criarmos CNPJ
		$faker->addProvider(new \Faker\Provider\pt_BR\PhoneNumber($faker)); // Para criarmos o telefone

        $criarQuantosFornecedores = 2000;

        $forenecedorPush = [];


		for($i = 0; $i < $criarQuantosFornecedores; $i++){

			array_push($forenecedorPush, [
				'razao' => $faker->unique()->company,
				'cnpj' => $faker->unique()->cnpj,
				'ie' => $faker->unique()->numberBetween(1000000, 9000000), //5793527
				'telefone' => $faker->unique()->cellphoneNumber,
				'cep' => $faker->postcode,
				'endereco' => $faker->streetName,
				'numero' => $faker->buildingNumber,
				'bairro' => $faker->city,
				'cidade' => $faker->city,
				'estado' => $faker->stateAbbr,
				'ativo'  => $faker->numberBetween(1, 0), // true ou false
				'criado_em' => $faker->dateTimeBetween('-2 month', '-1 days')->format('Y-m-d H:i:s'),
				'atualizado_em' => $faker->dateTimeBetween('-2 month', '-1 days')->format('Y-m-d H:i:s'),
			]);

		}


		//echo '<pre>';
		//print_r($forenecedorPush);
		//exit;


		$fornecedorModel->skipValidation(true)->insertBatch($forenecedorPush);


		echo "$criarQuantosFornecedores, semeados com sucesso!";

		
	}
}
