<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClienteFakerSeeder extends Seeder
{
	public function run()
	{
		
		$clienteModel = new \App\Models\ClienteModel();

		$usuarioModel = new \App\Models\UsuarioModel();

		$grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();


		// use a fábrica para criar uma instância Faker \ Generator
        $faker = \Faker\Factory::create('pt-BR');

		$faker->addProvider(new \Faker\Provider\pt_BR\Person($faker)); // Para criarmos CPF
		$faker->addProvider(new \Faker\Provider\pt_BR\PhoneNumber($faker)); // Para criarmos o telefone

        $criarQuantosClientes = 1000;


		for($i = 0; $i < $criarQuantosClientes; $i++){


			$nomeGerado = $faker->unique()->name;
			$emailGerado = $faker->unique()->email;


			$cliente = [
				'nome' => $nomeGerado, 
				'cpf' => $faker->unique()->cpf,
				'telefone' => $faker->cellphoneNumber,
				'email' => $emailGerado,
				'cep' => $faker->postcode,
				'endereco' => $faker->streetName,
				'numero' => $faker->buildingNumber,
				'bairro' => $faker->city,
				'cidade' => $faker->city,
				'estado' => $faker->stateAbbr,
			];


			// Criamos o cliente
			$clienteModel->skipValidation(true)->insert($cliente);



			// Montamos os dados do usuário do cliente
			$usuario = [
				'nome' => $nomeGerado,
				'email' => $emailGerado,
				'password' => '123456',
				'ativo'	   => true,
			];


			// Criamos o usuário do cliente
			$usuarioModel->skipValidation(true)->protect(false)->insert($usuario);



			// Montamos os dados do grupo que o usuário fará parte
			$grupoUsuario = [
				'grupo_id' => 2, // Grupo de clientes.... lembrem que esse ID jamais deverá ser alterado ou removido.
				'usuario_id' => $usuarioModel->getInsertID(),
			];


			// Inserimos o usuário no grupo de clientes
			$grupoUsuarioModel->protect(false)->insert($grupoUsuario);


			// Atualizamos a tabela de clientes com o ID do usuário criado
			$clienteModel
						->protect(false)
						->where('id', $clienteModel->getInsertID())
						->set('usuario_id', $usuarioModel->getInsertID())
						->update();
			
		} // fim for


		echo "$criarQuantosClientes clientes semeados com sucesso!";

	}
}
