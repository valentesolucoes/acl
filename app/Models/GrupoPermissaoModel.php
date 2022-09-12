<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoPermissaoModel extends Model
{	
	protected $table                = 'grupos_permissoes';
	protected $returnType           = 'object';
	protected $allowedFields        = ['grupo_id', 'permissao_id'];



	/**
	 * Método que recupera as permissões do grupo de acesso
	 *
	 * @param integer $grupo_id
	 * @param integer $quantidade_paginacao
	 * @return array|null
	 */
	public function recuperaPermissoesDoGrupo(int $grupo_id, int $quantidade_paginacao){


		$atributos = [
			'grupos_permissoes.id AS principal_id', // será utilizado como identificador na hora da remover a permissão do grupo
			'grupos.id AS grupo_id',
			'permissoes.id AS permissao_id',
			'permissoes.nome',
		];

		return $this->select($atributos)
					->join('grupos', 'grupos.id = grupos_permissoes.grupo_id')
					->join('permissoes', 'permissoes.id = grupos_permissoes.permissao_id')
					->where('grupos_permissoes.grupo_id', $grupo_id)
					->groupBy('permissoes.nome')
					->paginate($quantidade_paginacao);


	}

}
