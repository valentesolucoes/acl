<?php

namespace App\Models;

use CodeIgniter\Model;

class ContaPagarModel extends Model
{
	protected $table                = 'contas_pagar';
	protected $returnType           = 'App\Entities\ContaPagar';
	protected $allowedFields        = [
		'fornecedor_id',
		'valor_conta',
		'data_vencimento',
		'descricao_conta',
		'situacao',
	];

	// Dates
	protected $useTimestamps        = true;
	protected $createdField         = 'criado_em';
	protected $updatedField         = 'atualizado_em';

	protected $validationRules    = [
        'fornecedor_id'        => 'required', 
        'valor_conta'     => 'required|greater_than[0]',
        'data_vencimento'     => 'required',
        'descricao_conta'     => 'required',
    ];

    protected $validationMessages = [];


	// Callbacks
	protected $beforeInsert         = ['removeVirgulaValores'];
	protected $beforeUpdate         = ['removeVirgulaValores'];


	protected function removeVirgulaValores(array $data)
	{
		if (isset($data['data']['valor_conta'])){

			$data['data']['valor_conta'] = str_replace(",", "", $data['data']['valor_conta']);
		}

		return $data;
	}


	public function recuperaContasPagar(){

		$atributos = [
			'fornecedores.razao',
			'fornecedores.cnpj',
			'contas_pagar.*',
		];


		return $this->select($atributos)
					->join('fornecedores', 'fornecedores.id = contas_pagar.fornecedor_id')
					->orderBy('contas_pagar.situacao', 'ASC') // 0 - Contas em aberto
					->findAll();
	}


	public function buscaContaOu404(int $id = null){

		if($id === null){

			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a conta a pagar $id");
		}


		$atributos = [
			'fornecedores.razao',
			'fornecedores.cnpj',
			'contas_pagar.*',
		];


		$conta = $this->select($atributos)
					->join('fornecedores', 'fornecedores.id = contas_pagar.fornecedor_id')
					->find($id);

		if($conta === null){

			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a conta a pagar $id");
		}


		return $conta;


	}


	public function recuperaContasPagasOuAbertas(string $dataInicial, string $dataFinal, int $situacao){

		$campoDate = ($situacao === 0 ? 'criado_em' : 'atualizado_em');


		$dataInicial = str_replace('T', ' ', $dataInicial);
		$dataFinal = str_replace('T', ' ', $dataFinal);

        $atributos = [
            'fornecedores.razao',
            'fornecedores.cnpj',
            'contas_pagar.*',
        ];


		$where = 'contas_pagar.' .$campoDate. ' BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';        

        return $this->select($atributos)
                    ->join('fornecedores', 'fornecedores.id = contas_pagar.fornecedor_id')
                    ->where('contas_pagar.situacao', $situacao)
                    ->where($where)
                    ->orderBy('contas_pagar.situacao', 'ASC')
                    // ->getCompiledSelect();
                    ->findAll();
	}


	public function recuperaContasVencidas(){

		$atributos = [
            'fornecedores.razao',
            'fornecedores.cnpj',
            'contas_pagar.*',
        ]; 

        return $this->select($atributos)
                    ->join('fornecedores', 'fornecedores.id = contas_pagar.fornecedor_id')
                    ->where('contas_pagar.data_vencimento <', date('Y-m-d'))
                    ->where('contas_pagar.situacao', 0)
                    ->orderBy('contas_pagar.situacao', 'ASC')
                    //->getCompiledSelect();
                    ->findAll();
	}


	
}
