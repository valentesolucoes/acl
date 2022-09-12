<?php

namespace App\Libraries;

class Autenticacao {
    private $usuario;
    private $usuarioModel;
    private $grupoUsuarioModel;


    public function __construct()    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
    }

    /**
     * Método que realiza o login na aplicação
     *
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public function login(string $email, string $password): bool     {

        // Buscamos o usuário
        $usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);

        if($usuario === null){

            return false;
        }

        // Vericamos se a senha é válida
        if($usuario->verificaPassword($password) == false){

            return false;

        }

        // Verificamos se o usuário pode logar na aplicação
        if($usuario->ativo == false){            

            return false;

        }

        // Logamos o usuario na aplicação
        $this->logaUsuario($usuario);


        // Retornamos true, ou seja, o usuário pode logar tranquilamente
        return true;

    }

    /**
     * Método de logout
     *
     * @return void
     */
    public function logout(): void {
        session()->destroy();
    }

    public function pegaUsuarioLogado(){
        if($this->usuario === null){
            $this->usuario = $this->pegaUsuarioDaSessao();
        }
        return $this->usuario;
    }

    /**
     * Método que verifica se o usuário está logado
     *
     * @return boolean
     */
    public function estaLogado() : bool {
        return $this->pegaUsuarioLogado() !== null;
    }

    //--------------------Métodos privados----------------//

    /**
     * Método que insere na sessão o ID do usuário
     *
     * @param object $usuario
     * @return void
     */
    private function logaUsuario(object $usuario): void {
        // Recuperamos a instância da sessão
        $session = session();
        // Antes de inserirmos o ID do usuario na sessão,
        // devemos gerar um novo ID  da sessão
        $session->regenerate();
        // Setamos na sessão o ID do usuário
        $session->set('usuario_id', $usuario->id);
    }


    /**
     * Método que recupera da sessão e valida o usuário logado
     *
     * @return null|object
     */
    private function pegaUsuarioDaSessao(){
        if(session()->has('usuario_id') == false){
            return null;
        }

        // Busco usuário na base de dados
        $usuario = $this->usuarioModel->find(session()->get('usuario_id'));

        // Validamos se o usuario existe e se tem permissão de login na aplicação
        if($usuario == null || $usuario->ativo == false){
            return null;
        }

        // Definimos as permissões do usuário logado
        $usuario = $this->definePermissoesDoUsuarioLogado($usuario);

        // Retornamos o objeto $usuario
        return $usuario;
    }

    /**
     * Método que verifica se o usuário logado (session()->get('usuario_id')) está associado ao grupo de admin
     *
     * @return boolean
     */
    private function isAdmin() : bool {
        // Definimos o ID do grupo admin.
        // Não equeçam que esse ID jamais poderá ser alterado.
        // Por isso, nós defendemos no controller
        $grupoAdmin = 1;

        // Verificamos se o usuário logado está no grupo de admintrador
        $administrador = $this->grupoUsuarioModel->usuarioEstaNoGrupo($grupoAdmin, session()->get('usuario_id'));

        // Verificamos se foi encontrado o registro
        if($administrador == null){
            return false;
        }

        // Retornamos true, ou seja, o usuário logado faz parte do grupo admin
        return true;
    }
    
    /**
     * Método que verifica se o usuário logado (session()->get('usuario_id')) está associado ao grupo de clientes
     *
     * @return boolean
     */
    private function isCliente() : bool {
        // Definimos o ID do grupo cliente.
        // Não equeçam que esse ID jamais poderá ser alterado.
        // Por isso, nós defendemos no controller
        $grupoCliente = 2;

        // Verificamos se o usuário logado está no grupo de admintrador
        $cliente = $this->grupoUsuarioModel->usuarioEstaNoGrupo($grupoCliente, session()->get('usuario_id'));

        // Verificamos se foi encontrado o registro
        if($cliente == null){
            return false;
        }

        // Retornamos true, ou seja, o usuário logado faz parte do grupo admin
        return true;
    }

    /**
     * Método que define as permissões que o usuário logado possui.
     * Usado exclusivamente no método pegaUsuarioDaSessao()
     *
     * @param object $usuario
     * @return object
     */
    private function definePermissoesDoUsuarioLogado(object $usuario) : object {
        // Definimos se o usuário logado é admin
        // Esse atributo será utilizado no método temPermissaoPara() na Entity Usuario
        $usuario->is_admin = $this->isAdmin();

        // Se for admin, então não é cliente
        if($usuario->is_admin == true){
            $usuario->is_cliente = false;
        }else{
            // Nesse ponto, podemos verificar se o usuário logado é um cliente, visto que ele não é admin
            $usuario->is_cliente = $this->isCliente();
        }

        // Só recuperamos as permissões de um usuário que não seja admin e não seja cliente
        // pois esses dois grupos não possuem permissões
        // O atributo $usuario->permissoes será examinado na Entity Usuario para verificarmos se
        // o mesmo pode ou não visualizar e acessar alguma rota.
        // Notem que se o usuário logado possui o atributo $usuario->permissoes,
        // é porque ele não é admin e não é cliente
        if($usuario->is_admin == false && $usuario->is_cliente == false){
            $usuario->permissoes = $this->recuperaPermissoesDoUsuarioLogado();
        }

        // Nesse ponto já definimos se é admin ou se é cliente.
        // Caso não seja nem admin e nem cliente, então o objeto possui o atributo permissões,
        // que pode ou não estar vazio
        // Portanto, podemos retornar $usuario
        return $usuario;
    }

    /**
     * Método que retorna as permissões do usuário logado
     *
     * @return array
     */
    private function recuperaPermissoesDoUsuarioLogado() : array{
        $permissoesDoUsuario = $this->usuarioModel->recuperaPermissoesDoUsuarioLogado( session()->get('usuario_id'));
        return array_column($permissoesDoUsuario, 'permissao');
    }
}
