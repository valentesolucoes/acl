<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ContaPagar extends Entity
{
	
	protected $dates   = [
		'criado_em',
		'atualizado_em',
	];


	public function exibeSituacao() : string {


		if($this->situacao == 1){

			return '<i class="fa fa-check-circle text-success"></i>&nbsp;Conta foi paga em ' . date('d/m/Y', strtotime($this->atualizado_em));

		}

		if($this->data_vencimento == date('Y-m-d')){

			return '<i class="fa fa-check-circle text-warning"></i>&nbsp;Conta vencerá hoje ' . date('d/m/Y', strtotime($this->data_vencimento));

		}

		if($this->data_vencimento > date('Y-m-d')){

			return '<i class="fa fa-check-circle text-info"></i>&nbsp;Conta vencerá em ' . date('d/m/Y', strtotime($this->data_vencimento));

		}

		if($this->data_vencimento < date('Y-m-d') && $this->situacao == 0){

			return '<i class="fa fa-exclamation-triangle text-danger"></i>&nbsp;Conta venceu em ' . date('d/m/Y', strtotime($this->data_vencimento));

		}


	}


	public function defineDataVencimentoEvento() : int {

		$dataAtualConvertida = $this->mutateDate(date('Y-m-d'));

		return $dataAtualConvertida->difference($this->data_vencimento)->getDays();
	}
	
}
