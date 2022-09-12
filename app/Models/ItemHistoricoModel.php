<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemHistoricoModel extends Model
{
    protected $table                = 'itens_historico';
    protected $returnType           = 'object';
    protected $allowedFields        = [
        'usuario_id',
        'item_id',
        'acao',
        'atributos_alterados',
    ];

    public function recuperaHistoricoItem(int $item_id){

        $atributos = [
            'atributos_alterados',
            'itens_historico.criado_em',
            'acao',
            'usuarios.nome as usuario'
        ];

        return $this
                    ->asArray()
                    ->select($atributos)
                    ->join('usuarios', 'usuarios.id = itens_historico.usuario_id')
                    ->where('item_id', $item_id)
                    ->orderBy('itens_historico.criado_em', 'DESC')
                    ->findAll();

    }
}
