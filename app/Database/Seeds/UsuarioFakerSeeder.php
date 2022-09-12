<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioFakerSeeder extends Seeder
{
    public function run()
    {
        $usuarioModel = new \App\Models\UsuarioModel();


        // use a fábrica para criar uma instância Faker \ Generator
        $faker = \Faker\Factory::create();

        $criarQuantosUsuarios = 5000;

        $usuariosPush = [];

        for ($i = 0; $i < $criarQuantosUsuarios; $i++) {
            array_push($usuariosPush, [
                'nome' => $faker->unique()->name,
                'email' => $faker->unique()->email,
                'password_hash' => '123456',// alterar o fake seeder quando conhecermos como criptografar a senha (hash)
                'ativo' => $faker->numberBetween(0, 1), // true ou false
            ]);
        }


		// echo '<pre>';
		// print_r($usuariosPush);
		// exit;


        $usuarioModel->skipValidation(true) // bypass na validação
                     ->protect(false) // bypass na proteção dos campos allowedFields
                     ->insertBatch($usuariosPush);


        echo "$criarQuantosUsuarios usuários criados com sucesso!";

        

        
                    
    }
}
