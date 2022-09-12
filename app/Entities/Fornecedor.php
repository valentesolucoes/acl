<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Fornecedor extends Entity
{
	
	protected $dates   = [
		'criado_em',
		'atualizado_em',
		'deletado_em',
	];

	public function exibeSituacao()
    {
        if ($this->deletado_em != null) {

            // Fornecedor excluído

            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';

            $situacao = anchor("fornecedores/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-succes btn-sm']);

            return $situacao;
        }

		if($this->ativo == true){

			return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativo';
		}


		if($this->ativo == false){

			return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativo';
		}
    }
	
}
