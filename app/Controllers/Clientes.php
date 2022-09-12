<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Traits\ValidacoesTrait;

use App\Entities\Cliente;

class Clientes extends BaseController
{
    use ValidacoesTrait;

    private $clienteModel;
    private $usuarioModel;
    private $grupoUsuarioModel;

    public function __construct()
    {
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
    }

    public function index()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Listando os clientes',
        ];

        return view('Clientes/index', $data);
    }


    public function recuperaClientes()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $atributos = [
            'id',
            'nome',
            'cpf',
            'email',
            'telefone',
            'deletado_em'
        ];

        $clientes = $this->clienteModel->select($atributos)
                                       ->withDeleted(true)
                                       ->orderBy('id', 'DESC')
                                       ->findAll();

        // Receberá o array de objetos de clientes
        $data = [];

        foreach ($clientes as $cliente) {
            $data[] = [
                'nome' => anchor("clientes/exibir/$cliente->id", esc($cliente->nome), 'title="Exibir cliente '.esc($cliente->nome).' "'),
                'cpf' => esc($cliente->cpf),
                'email' => esc($cliente->email),
                'telefone' => esc($cliente->telefone),
                'situacao' => $cliente->exibeSituacao(),
            ];
        }


        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('criar-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $cliente = new Cliente();

        $this->removeBlockCepEmailSessao();

        $data = [
            'titulo' => 'Criando novo cliente',
            'cliente' => $cliente
        ];

        return view('Clientes/criar', $data);
    }

    public function cadastrar(int $id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        if (session()->get('blockEmail') === true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um E-mail com domínio válido'];

            return $this->response->setJSON($retorno);
        }


        if (session()->get('blockCep') === true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];

            return $this->response->setJSON($retorno);
        }

        // Recupero o post da requisição
        $post = $this->request->getPost();

        $cliente = new Cliente($post);


        if ($this->clienteModel->save($cliente)) {

            // Cria usuario do cliente
            $this->criaUsuarioParaCliente($cliente);

            // Envia dados de acesso ao clente
            $this->enviaEmailCriacaoEmailAcesso($cliente);

    
            $btnCriar = anchor("clientes/criar", 'Cadastrar novo cliente', ['class' => 'btn btn-danger mt-2']);
            
            session()->setFlashdata('sucesso', "Dados salvos com sucesso!<br><br>Importante: informe ao cliente os dados de acesso ao sistema: <p>E-mail: $cliente->email <p><p>Senha inicial: 123456</p> Esses mesmos dados foram enviados para o e-mail do cliente.<br> $btnCriar");

            $retorno['id'] = $this->clienteModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->clienteModel->errors();

        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function exibir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $cliente = $this->buscaclienteOu404($id);

        $data = [
            'titulo' => 'Exibindo o cliente '. esc($cliente->nome),
            'cliente' => $cliente
        ];

        return view('Clientes/exibir', $data);
    }


    public function editar(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $cliente = $this->buscaclienteOu404($id);

        $this->removeBlockCepEmailSessao();

        $data = [
            'titulo' => 'Editando o cliente '. esc($cliente->nome),
            'cliente' => $cliente
        ];

        return view('Clientes/editar', $data);
    }


    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        // Recupero o post da requisição
        $post = $this->request->getPost();

        $cliente = $this->buscaclienteOu404($post['id']);


        if (session()->get('blockEmail') === true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um E-mail com domínio válido'];

            return $this->response->setJSON($retorno);
        }


        if (session()->get('blockCep') === true) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];

            return $this->response->setJSON($retorno);
        }


        $cliente->fill($post);


        if ($cliente->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }


        if ($this->clienteModel->save($cliente)) {
            if ($cliente->hasChanged('email')) {
                $this->usuarioModel->atualizaEmailDoCliente($cliente->usuario_id, $cliente->email);

                $this->enviaEmailAlteracaoEmailAcesso($cliente);

                session()->setFlashdata('sucesso', 'Dados salvos com sucesso!<br><br>Importante: informe ao cliente o novo e-mail de acesso ao sistema: <p>E-mail: '.$cliente->email.'<p> Um e-mail de notificação foi enviado para o cliente');


                return $this->response->setJSON($retorno);
            }


            // Não houve alteração no e-mail

            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->clienteModel->errors();

        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function consultaCep()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        $cep = $this->request->getGet('cep');

        
        return $this->response->setJSON($this->consultaViaCep($cep));
    }

    public function consultaEmail()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        $email = $this->request->getGet('email');

        // Cuidado para não deixar o bypass ativado (true)
        return $this->response->setJSON($this->checkEmail($email, true));
    }


    public function historico(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $cliente = $this->buscaclienteOu404($id);


        $data = [
            'titulo' => 'Histórico de atendimento do cliente '. esc($cliente->nome),
            'cliente' => $cliente
        ];


        $ordemModel = new \App\Models\OrdemModel();

        $ordensCliente = $ordemModel->where('cliente_id', $cliente->id)->orderBy('ordens.id', 'DESC')->paginate(5);

        if($ordensCliente != null){

            $data['ordensCliente'] = $ordensCliente;
            $data['pager'] = $ordemModel->pager;

        }


        return view('Clientes/historico', $data);
    }


    public function excluir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('excluir-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $cliente = $this->buscaClienteOu404($id);

        if ($cliente->deletado_em != null) {
            return redirect()->back()->with('info', "Cliente $cliente->nome já encontra-se excluído");
        }

        if ($this->request->getMethod() === 'post') {
            $this->clienteModel->delete($id);

            return redirect()->to(site_url("clientes"))->with('sucesso', "Cliente $cliente->nome excluído com sucesso!");
        }

        $data = [
            'titulo' => "Excluindo o cliente ".esc($cliente->nome),
            'cliente' => $cliente,
        ];

        return view('Clientes/excluir', $data);
    }

    public function desfazerExclusao(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-clientes')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $cliente = $this->buscaClienteOu404($id);

        if ($cliente->deletado_em === null) {
            return redirect()->back()->with('info', "Apenas clientes excluídos podem ser recuperados");
        }


        $cliente->deletado_em = null;
        $this->clienteModel->protect(false)->save($cliente);

        return redirect()->back()->with('sucesso', "Cliente $cliente->nome recuperado com sucesso!");
    }



    /*--------------------------------Método privados-------------------------*/


    /**
     * Método que recupera o cliente
     *
     * @param integer $id
     * @return Exceptions|object
     */
    private function buscaclienteOu404(int $id = null)
    {
        if (!$id || !$cliente = $this->clienteModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o cliente $id");
        }

        return $cliente;
    }


    /**
     * Método que envia o e-mail para o cliente informando a alteração no e-mail de acesso.
     *
     * @param object $usuario
     * @return void
     */
    private function enviaEmailCriacaoEmailAcesso(object $cliente) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem');

        $email->setTo($cliente->email);
   
        $email->setSubject('Dados de acesso ao sistema');


        $data = [
            'cliente' => $cliente
        ];

        $mensagem = view('Clientes/email_dados_acesso', $data);

        $email->setMessage($mensagem);

        $email->send();
    }


    /**
     * Método que envia o e-mail para o cliente informando a alteração no e-mail de acesso.
     *
     * @param object $usuario
     * @return void
     */
    private function enviaEmailAlteracaoEmailAcesso(object $cliente) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem');

        $email->setTo($cliente->email);
   
        $email->setSubject('E-mail de acesso ao sistema foi alterado');


        $data = [
            'cliente' => $cliente
        ];

        $mensagem = view('Clientes/email_acesso_alterado', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    /**
     * Remove da sessão os dados de bloqueio de CEP e E-mail das requisições anterioes
     *
     * @return void
     */
    private function removeBlockCepEmailSessao() : void
    {
        session()->remove('blockCep');
        session()->remove('blockEmail');
    }


    /**
     * Método que cria o usuário para o cliente recém cadastrado
     *
     * @param object $cliente
     * @return void
     */
    private function criaUsuarioParaCliente(object $cliente) : void
    {

            // Montamos os dados do usuário do cliente
        $usuario = [
                'nome' => $cliente->nome,
                'email' => $cliente->email,
                'password' => '123456',
                'ativo'	   => true,
            ];


        // Criamos o usuário do cliente
        $this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);



        // Montamos os dados do grupo que o usuário fará parte
        $grupoUsuario = [
                'grupo_id' => 2, // Grupo de clientes.... lembrem que esse ID jamais deverá ser alterado ou removido.
                'usuario_id' => $this->usuarioModel->getInsertID(),
            ];


        // Inserimos o usuário no grupo de clientes
        $this->grupoUsuarioModel->protect(false)->insert($grupoUsuario);


        // Atualizamos a tabela de clientes com o ID do usuário criado
        $this->clienteModel
                        ->protect(false)
                        ->where('id', $this->clienteModel->getInsertID())
                        ->set('usuario_id', $this->usuarioModel->getInsertID())
                        ->update();
    }
}
