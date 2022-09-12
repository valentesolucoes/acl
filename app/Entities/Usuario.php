<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

use App\Libraries\Token;

class Usuario extends Entity
{
    protected $dates   = [
        'criado_em',
        'atualizado_em',
        'deletado_em',
    ];


    public function exibeSituacao()
    {
        if ($this->deletado_em != null) {

            // Usuário excluído

            $icone = '<span class="text-white">Excluído</span>&nbsp;<i class="fa fa-undo"></i>&nbsp;Desfazer';

            $situacao = anchor("usuarios/desfazerexclusao/$this->id", $icone, ['class' => 'btn btn-outline-succes btn-sm']);

            return $situacao;
        }

		if($this->ativo == true){

			return '<i class="fa fa-unlock text-success"></i>&nbsp;Ativo';
		}


		if($this->ativo == false){

			return '<i class="fa fa-lock text-warning"></i>&nbsp;Inativo';
		}
    }

    /**
     * Método que verifica se a senha é valida
     *
     * @param string $password
     * @return boolean
     */
    public function verificaPassword(string $password):bool {

        return password_verify($password, $this->password_hash);
    }

    /**
     * Método que valida se o usuário logado possui a permissão para visualizar / acessar determinada rota.
     *
     * @param string $permissao
     * @return boolean
     */
    public function temPermissaoPara(string $permissao) : bool {

        // Se o usuario logado é admin, retornamos true
        if($this->is_admin == true){
            return true;
        }

        // Se o usuário logado ($this) possui o atributo 'permissoes' vazio (empty),
        // então retornamos false também, pois a $permissao não estará no array $permissoes, beleza?
        // Isso acontece quando o usuário logado ($this) faz parte de um grupo que não possui permissões
        // Ou não está em nenhum grupo de acesso
        // Não esqueçam que essa regra não válida para clientes,
        // pois na classe Autenticação definimos se o usuário logado é um cliente ou admin
        if(empty($this->permissoes)){

            return false;

        }


        // Nesse ponto o usuário logado possui permissões,
        // Então podemos verificar tranquilamente
        if(in_array($permissao, $this->permissoes) == false){

            return false;

        }


        // Retornamos o true, pois a permissão é válida
        return true;

    }



    /**
     * Método que inicia a recuperação de senha
     *
     * @return void
     */
    public function iniciaPasswordReset() : void {

        $token = new Token();


        $this->reset_token = $token->getValue();


        $this->reset_hash = $token->getHash();


        $this->reset_expira_em = date('Y-m-d H:i:s', time() + 7200);

    }


    /**
     * Método que finaliza o processo de redefinição de senha.
     *
     * @return void
     */
    public function finalizaPasswordReset() : void {

        $this->reset_hash = null;
        $this->reset_expira_em = null;

    }

}
