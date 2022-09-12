<?php

namespace App\Controllers;

class Seed extends \CodeIgniter\Controller
{
    public function index()
    {
        $seeder = \Config\Database::seeder();

        try {
            
            $seeder->call('HospedarSeeder');

            echo 'Dados iniciais criados com sucesso!';

        } catch (\Throwable $e) {
            
            echo $e->getMessage();
        }
    }
}
