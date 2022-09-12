<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioAdminSeeder extends Seeder
{
    public function run()
    {
        $grupoModel = new \App\Models\GrupoModel();

        $grupos = [

            [
                'nome' => 'Administrador',
                'exibir' => false, // 0
                'descricao' => 'Grupo com acesso total ao sistema', // false
            ],
            [
                'nome' => 'Clientes',
                'exibir' => false, // 0
                'descricao' => 'Acessa o sistema como cliente para visualizar apenas as suas ordens de serviços.', // false
            ],

        ];

        foreach ($grupos as $grupo) {
            $grupoModel->skipValidation(true)->protect(false)->insert($grupo);
        }


        // Segunda parte.... criamos o usuário admin


        $usuarioModel = new \App\Models\UsuarioModel();

        $usuario = [
            'nome' => 'Usuario admin',
            'email' => 'admin@admin.com',
            'password' => '123456',
            'ativo' => true,
        ];

		$usuarioModel->skipValidation(true)->protect(false)->insert($usuario);


		// Terceiro parte.... inserimos o usuário no grupo de administrador

		$grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();


		$grupoUsuario = [ 
			'grupo_id' => 1,
			'usuario_id' => $usuarioModel->getInsertID(),
		];

		$grupoUsuarioModel->protect(false)->insert($grupoUsuario);


		echo 'Usuario admin semeado com sucesso!';
    }
}
