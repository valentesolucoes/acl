<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrdemFakerSeeder extends Seeder
{
    public function run()
    {
        $clienteModel = new \App\Models\ClienteModel();
        $ordemModel = new \App\Models\OrdemModel();
        $ordemResponsavelModel = new \App\Models\OrdemResponsavelModel();


        // Recupero apenas os ID's dos clientes
        $clientes = $clienteModel->select('id')->findAll();


        $clientesIDs = array_column($clientes, 'id');

        // use a fábrica para criar uma instância Faker \ Generator
        $faker = \Faker\Factory::create('pt-BR');


        helper('text');

        for ($i = 0; $i < count($clientesIDs); $i++) {
            $ordem = [
                'cliente_id' => $faker->randomElement($clientesIDs),
                'codigo' => $ordemModel->geraCodigoOrdem(),
                'situacao' => 'aberta',
                'equipamento' => $faker->name(),
                'defeito' => $faker->realText(),
            ];

            // Inserimos a ordem
            $ordemModel->skipValidation(true)->insert($ordem);


            $ordemResponsavel = [

                'ordem_id' => $ordemModel->getInsertID(),
                'usuario_abertura_id' => 5001,

            ];

            // Cadastro usuário que abriu a ordem
            $ordemResponsavelModel->skipValidation(true)->insert($ordemResponsavel);
        }


        echo count($clientesIDs). ' ordens criadas com sucesso!';
    }
}
