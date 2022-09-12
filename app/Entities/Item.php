<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Item extends Entity
{
	
	protected $dates   = [
		'criado_em',
		'atualizado_em',
		'deletado_em',
	];


	public function exibeSituacao()
    {
        if ($this->deletado_em != null) {

            // Item excluído

            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';

            $situacao = anchor("itens/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-succes btn-sm']);

            return $situacao;
        }

		if($this->ativo == true){

			return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativo';
		}


		if($this->ativo == false){

			return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativo';
		}
    }


	public function exibeTipo(){


		$tipoItem = "";

		if($this->tipo === 'produto'){

			$tipoItem = '<i class="fa fa-archive text-success" aria-hidden="true"></i>&nbsp;Produto';
		}else{

			$tipoItem = '<i class="fa fa-wrench text-white" aria-hidden="true"></i>&nbsp;Serviço';

		}


		return $tipoItem;

	}

	public function exibeEstoque(){

		return ($this->tipo === 'produto' ? $this->estoque : 'Não se aplica');

	}


	public function recuperaAtributosAlterados() : string {

		$atributosAlterados = [];


		if($this->hasChanged('nome')){

			$atributosAlterados['nome'] = "O nome foi alterado para $this->nome";

		}

		if($this->hasChanged('preco_custo')){

			$atributosAlterados['preco_custo'] = "O preço de custo foi alterado para $this->preco_custo";

		}

		if($this->hasChanged('preco_venda')){

			$atributosAlterados['preco_venda'] = "O preço de venda foi alterado para $this->preco_venda";

		}

		if($this->hasChanged('estoque')){

			$atributosAlterados['estoque'] = "O estoque foi alterado para $this->estoque";

		}

		if($this->hasChanged('descricao')){

			$atributosAlterados['descricao'] = "A descrição foi alterada para $this->descricao";

		}

		if($this->hasChanged('controla_estoque')){

			if($this->controla_estoque == true){
				$atributosAlterados['controla_estoque'] = "O controle de estoque foi ativado";
			}else{
				$atributosAlterados['controla_estoque'] = "O controle de estoque não está mais ativo";
			}
		}

		if($this->hasChanged('ativo')){

			if($this->ativo == true){
				$atributosAlterados['ativo'] = "O item está ativo";
			}else{
				$atributosAlterados['ativo'] = "O item não está mais ativo";
			}
		}

		return serialize($atributosAlterados);

	}
	
}
