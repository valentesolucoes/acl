<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table                = 'clientes';
    protected $returnType           = 'App\Entities\Cliente';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'usuario_id',
        'nome',
        'cpf',
        'telefone',
        'email',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'criado_em';
    protected $updatedField         = 'atualizado_em';
    protected $deletedField         = 'deletado_em';

    // Validation
    protected $validationRules    = [
        'nome'         => 'required|min_length[3]|max_length[125]',
        'email'        => 'required|valid_email|max_length[230]|is_unique[clientes.email,id,{id}]', // Não pode ter espaços
        'email'        => 'is_unique[usuarios.email,id,{id}]', // Também validamos se o e-mail informado não existe na tabela de usuários..... admin@admin.com
        'telefone'        => 'required|exact_length[15]|is_unique[clientes.telefone,id,{id}]', // Tamanho exato requerido pela Gerencianet
        'cpf'        => 'required|exact_length[14]|validaCPF|is_unique[clientes.cpf,id,{id}]',
        'cep'        => 'required|exact_length[9]',
    ];

    protected $validationMessages = [];
}
