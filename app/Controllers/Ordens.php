<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Ordem;
use App\Traits\OrdemTrait;

// reference the Dompdf namespace
use Dompdf\Dompdf;

// Para encerrar a ordem com boleto bancário
use App\Transacao\Gerencianet\Operacoes;

class Ordens extends BaseController
{
    use OrdemTrait;

    private $ordemModel;
    private $transacaoModel;
    private $clienteModel;
    private $ordemResponsavelModel;
    private $usuarioModel;
    private $formaPagamentoModel;
    private $itemModel;

    public function __construct()
    {
        $this->ordemModel = new \App\Models\OrdemModel();
        $this->transacaoModel = new \App\Models\TransacaoModel();
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->ordemResponsavelModel = new \App\Models\OrdemResponsavelModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->formaPagamentoModel = new \App\Models\FormaPagamentoModel();
        $this->itemModel = new \App\Models\ItemModel();
    }

    public function index()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')){

            $this->registraAcaoDoUsuario('tentou listar as ordens de serviço');

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Listando as ordens de serviços'
        ];

        return view('Ordens/index', $data);
    }


    public function recuperaOrdens()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        

        $ordens = $this->ordemModel->recuperaOrdens();

        // Receberá o array de objetos de ordems
        $data = [];

        foreach ($ordens as $ordem) {
            $data[] = [
                'codigo' => anchor("ordens/detalhes/$ordem->codigo", esc($ordem->codigo), 'title="Exibir ordem '.esc($ordem->codigo).' "'),
                'nome' => esc($ordem->nome),
                'cpf' => esc($ordem->cpf),
                'criado_em' => esc($ordem->criado_em->humanize()),
                'situacao' => $ordem->exibeSituacao(),
            ];
        }


        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }


    public function criar()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('criar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = new Ordem();

        $ordem->codigo = $this->ordemModel->geraCodigoOrdem();

        $data = [
            'titulo' => 'Cadastrando nova ordem de serviço',
            'ordem' => $ordem
        ];

        return view('Ordens/criar', $data);
    }


    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        // Recupero o post da requisição
        $post = $this->request->getPost();


        $ordem = new Ordem($post);

        if ($this->ordemModel->save($ordem)) {
            $this->finalizaCadastroDaOrdem($ordem);

            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

            $retorno['codigo'] = $ordem->codigo;

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->ordemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }



    /**
     * Método que recupera os clientes para serem renderizados via selectize.js e ajax request
     *
     * @return response
     */
    public function buscaClientes()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $atributos = [
            'id',
            'CONCAT(nome, " CPF ", cpf) AS nome',
            'cpf',
        ];

        $termo = $this->request->getGet('termo');

        $clientes = $this->clienteModel->select($atributos)
                                              ->asArray()
                                              ->like('nome', $termo)
                                              ->orLike('cpf', $termo)
                                              ->orderBy('nome', 'ASC')
                                              ->findAll();

        return $this->response->setJSON($clientes);
    }


    public function detalhes(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        // Invocando o OrdemTrait
        $this->preparaItensDaOrdem($ordem);

        
        // Verifico se essa ordem possui uma transação
        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        // Se a ordem possui uma transação (boleto), então criamos um atributo transação com os valores da mesma
        if ($transacao !== null) {
            $ordem->transacao = $transacao;
        }

        $data = [
            'titulo' => "Detalhando a ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];

        return view('Ordens/detalhes', $data);
    }


    public function editar(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        
        if ($ordem->situacao === 'encerrada') {
            return redirect()->back()->with("info", "Esta ordem não pode ser editada, pois encontra-se ".ucfirst($ordem->situacao));
        }
    

        $data = [
            'titulo' => "Editando a ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];

        return view('Ordens/editar', $data);
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


        // Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);

        
        if ($ordem->situacao === 'encerrada') {

            // Retornamos os erros de validação
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['situacao' => "Esta ordem não pode ser editada, pois encontra-se ".ucfirst($ordem->situacao)];

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $ordem->fill($post);


        if ($ordem->hasChanged() === false) {
            $retorno['info'] = 'Não dados para serem atualizados';
            return $this->response->setJSON($retorno);
        }


        if ($this->ordemModel->save($ordem)) {
            if (session()->has('ordem-encerrar')) {
                session()->setFlashdata('sucesso', 'Agora já é possível encerrar a ordem de serviço!');

                $retorno['redirect'] = "ordens/encerrar/$ordem->codigo";
            
                return $this->response->setJSON($retorno);
            }

            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->ordemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function responsavel(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        
        if ($ordem->situacao === 'encerrada') {
            return redirect()->back()->with("info", "Esta ordem já encontra-se ".ucfirst($ordem->situacao));
        }
    

        $data = [
            'titulo' => "Definindo o responsável técnico pela ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];

        return view('Ordens/responsavel', $data);
    }


    public function buscaResponsaveis()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        $termo = $this->request->getGet('termo');

        $responsaveis = $this->usuarioModel->recuperaResponsaveisParaOrdem($termo);

        return $this->response->setJSON($responsaveis);
    }


    public function definirResponsavel()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');


        $regras = [
            'usuario_responsavel_id' => 'required|greater_than[0]',
        ];

        $mensagens = [   // Errors
            'usuario_responsavel_id' => [
                'required' => 'Por favor pesquise um Responsável técnico e tente novamente.',
                'greater_than' => 'Por favor pesquise um Responsável técnico e tente novamente.',
            ],
        ];

        $validacao->setRules($regras, $mensagens);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        // Recupero o post da requisição
        $post = $this->request->getPost();
        

        // Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);


        if ($ordem->situacao === 'encerrada') {

            // Retornamos os erros de validação
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['situacao' => "Esta ordem não pode ser editada, pois encontra-se ".ucfirst($ordem->situacao)];

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        // Validamos a existência do usuarioResponsavel
        $usuarioResponsavel = $this->buscaUsuarioOu404($post['usuario_responsavel_id']);


        if ($this->ordemResponsavelModel->defineUsuarioResponsavel($ordem->id, $usuarioResponsavel->id)) {
            if (session()->has('ordem-encerrar')) {
                session()->setFlashdata('sucesso', 'Agora já é possível encerrar a ordem de serviço!');

                $retorno['redirect'] = "ordens/encerrar/$ordem->codigo";
            
                return $this->response->setJSON($retorno);
            }

            session()->setFlashdata('sucesso', 'Técnico responsável definido com sucesso!');

            $retorno['redirect'] = "ordens/responsavel/$ordem->codigo";
            
            return $this->response->setJSON($retorno);
        }


        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->ordemResponsavelModel->errors();

        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }

    public function email(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        // Invocando o OrdemTrait
        $this->preparaItensDaOrdem($ordem);


        if ($ordem->situacao === 'aberta') {
            $this->enviaOrdemEmAndamentoParaCliente($ordem);
        } else {
            $this->enviaOrdemEncerradaParaCliente($ordem);
        }

        return redirect()->back()->with('sucesso', 'Ordem enviada para o e-mail do cliente');
    }


    public function gerarPdf(string $codigo = null)
    {
        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);


        $this->preparaItensDaOrdem($ordem);

        $data = [
            'titulo' => "Gerar PDF da ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];


        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('Ordens/gerar_pdf', $data));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("detalhes-da-ordem-$ordem->codigo.pdf", ["Attachment" =>false]); // para exibir no navegador

        exit();
    }


    public function encerrar(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('encerrar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao !== 'aberta') {
            return redirect()->back()->with('atencao', 'Apenas ordens em aberto podem ser encerradas');
        }


        // Nesse ponto podemos definir na sessão a chave abaixo, pois usaremos a mesma
        // para redirecionar para o encerramento, bem como para o parecer técnico.
        session()->set('ordem-encerrar', $ordem->codigo);


        if ($ordem->parecer_tecnico === null || empty($ordem->parecer_tecnico)) {
            return redirect()->to(site_url("ordens/editar/$ordem->codigo"))->with('atencao', 'Por favor informe qual é o Parecer Técnico da Ordem');
        }


        if (! $this->ordemTemResponsavel($ordem->id)) {
            return redirect()->to(site_url("ordens/responsavel/$ordem->codigo"))->with('atencao', 'Escolha um responsável técnico antes de encerrar a ordem de serviço');
        }


        $this->preparaItensDaOrdem($ordem);


        $data = [
            'titulo' => "Encerrar a ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];


        if ($ordem->itens !== null) {

            // Ordem tem pelo menos 1 item, logo, ela tem valor
            // Retornamos todas as formas ativas, menos a Cortesia
            $data['formasPagamentos'] = $this->formaPagamentoModel->where('id !=', 2)->where('ativo', true)->findAll();

            $data['descontoBoleto'] = env('gerenciaNetDesconto') / 100 . '%';
        } else {

            // Ordem não gerou valor, pois não possui nenhum item
            // Envio apenas a forma Cortesia
            $data['formasPagamentos'] = $this->formaPagamentoModel->where('id', 2)->findAll();
        }

        
        return view('Ordens/encerrar', $data);
    }

    public function processaEncerramento()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        // Recupero o post da requisição
        $post = $this->request->getPost();


        $validacao = service('validation');


        $regras = [
            'forma_pagamento_id' => 'required',
        ];

        $mensagens = [   // Errors
            'forma_pagamento_id' => [
                'required' => 'Por favor escolha a forma de pagamento.',
            ],
        ];

        $validacao->setRules($regras, $mensagens);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }

        $formaPagamento = $this->formaPagamentoModel->where('ativo', true)->find($post['forma_pagamento_id']);

        if ($formaPagamento === null) {
            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['forma' => 'Não encontramos a forma de pagamento escolhida. Tente novamente.'];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }

        if ($formaPagamento->id == 1) {
            if (empty($post['data_vencimento']) || $post['data_vencimento'] == "") {
                $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['data_vencimento' => 'Para a forma de pagamento <b class="text-white">Boleto Bancário</b>, por favor informe a <b class="text-white">Data de vencimento</b>'];


                // Retorno para o ajax request
                return $this->response->setJSON($retorno);
            }


            if ($post['data_vencimento'] < date('Y-m-d')) {
                $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['data_vencimento' => 'Para a forma de pagamento <b class="text-white">Boleto Bancário</b>, a Data de vencimento <b class="text-white">não pode</b> ser menor que a Data atual'];


                // Retorno para o ajax request
                return $this->response->setJSON($retorno);
            }
        }


        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);
        

        $this->preparaItensDaOrdem($ordem);

        // Pagamento com boleto
        if ($formaPagamento->id == 1 && $ordem->itens !== null) {

            // Utilizado pela Gerencianet para definir a data vencimento do boleto
            $ordem->data_vencimento = $post['data_vencimento'];

            $objetoOperacao = new Operacoes($ordem, $formaPagamento);

            // Registramos o Boleto
            $objetoOperacao->registraBoleto();

            
            if (isset($ordem->erro_transacao)) {
                $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['erro_transacao' => $ordem->erro_transacao];


                // Retorno para o ajax request
                return $this->response->setJSON($retorno);
            }

            $href = $ordem->transacao->pdf;

            $btnBoleto = anchor("$href", 'Imprmir o Boleto', ['class' => 'btn btn-danger bagde btn-sm mt-2', 'target' => '_blank']);

            session()->setFlashdata('sucesso', 'Boleto registrado com sucesso com vencimento em '.date('d/m/Y', strtotime($ordem->data_vencimento)).'! <br> Aproveite para '.$btnBoleto);

            // Foi definido no método encerrar
            session()->remove('ordem-encerrar');

            return $this->response->setJSON($retorno);
        }


        // Outras formas de pagamento



        $this->preparaOrdemParaEncerrar($ordem, $formaPagamento);


        if($this->ordemModel->save($ordem)){

        
             if(isset($ordem->produtos) && $formaPagamento->id > 2){

                $this->itemModel->realizaBaixaNoEstoqueDeProdutos($ordem->produtos);

             }
            


            $this->ordemResponsavelModel->defineUsuarioEncerramento($ordem->id, usuario_logado()->id);

            session()->setFlashdata('sucesso', 'Ordem encerrada com sucesso!');

            // Foi definido no método encerrar
            session()->remove('ordem-encerrar');


            // Faço o unserialize, pois a view ordem_encerrada_email precisa percorrer os itens em um foreach.
            if($ordem->itens !== null){

                $ordem->itens = unserialize($ordem->itens);

            }

            $this->enviaOrdemEncerradaParaCliente($ordem);


            return $this->response->setJSON($retorno);

        }


        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->ordemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    
    }


    public function inserirDesconto()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        //[codigo] => RLAJBT2IANSMCJE86RBH
        //[valor_desconto] => 1,500.00

        // Recupero o post da requisição
        $post = $this->request->getPost();


        $validacao = service('validation');


        $regras = [
            'valor_desconto' => 'required',
        ];

        $mensagens = [   // Errors
            'valor_desconto' => [
                'required' => 'Por favor informe o valor de desconto maior que zero.',
            ],
        ];

        $validacao->setRules($regras, $mensagens);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $valorDesconto = str_replace([',', '.'], '', $post['valor_desconto']);

        
        if ($valorDesconto <= 0) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['valor_desconto' => 'Por favor informe o valor de desconto maior que zero.'];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        // Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);


        $ordem->valor_desconto = str_replace(',', '', $post['valor_desconto']);


        if ($ordem->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        if ($this->ordemModel->save($ordem)) {
            $this->defineMensagensDesconto($ordem->valor_desconto);

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }



        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->ordemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function removerDesconto()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        // Recupero o post da requisição
        $post = $this->request->getPost();

        // Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);


        $ordem->valor_desconto = null;


        if ($ordem->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        if ($this->ordemModel->save($ordem)) {
            session()->setFlashdata('sucesso', "Desconto removido com sucesso!");

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }



        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->ordemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function minhas()
    {

        /// quem cuidará da proteção dessa rota será o ClienteFilter

        /// portanto, não precisa colocar o ACL

        $data = [
            'titulo' => 'Listando as minhas ordens de serviços'
        ];

        return view('Ordens/minhas', $data);
    }


    public function recuperaOrdensCliente()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        

        $ordens = $this->ordemModel->recuperaOrdensClienteLogado(usuario_logado()->id);

        // Receberá o array de objetos de ordems
        $data = [];

        foreach ($ordens as $ordem) {
            $data[] = [
                $ordem->codigo = anchor("ordens/exibirordemcliente/$ordem->codigo", esc($ordem->codigo), 'title="Exibir ordem '.esc($ordem->codigo).' "'),
                esc($ordem->nome),
                esc($ordem->cpf),
                esc($ordem->criado_em->humanize()),
                $ordem->exibeSituacao(),
            ];
        }


        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }
    

    public function exibirOrdemCliente(string $codigo = null)
    {
        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if( ! usuario_logado()->is_admin){

            if($ordem->cliente_usuario_id != usuario_logado()->id){

                return redirect()->back()->with('atencao', "Não encontramos a ordem de serviço $codigo");

            }

        }


        $evidenciaModel = new \App\Models\OrdemEvidenciaModel();

        $evidencias = $evidenciaModel->where('ordem_id', $ordem->id)->findAll();


        if($evidencias != null){

            $ordem->evidencias = $evidencias;

        }


        // Invocando o OrdemTrait
        $this->preparaItensDaOrdem($ordem);


        $data = [
            'titulo' => "Detalhando a minha ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];

        return view('Ordens/exibir_ordem_cliente', $data);
    }


    public function excluir(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('excluir-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        
        if ($ordem->deletado_em != null) {
            return redirect()->back()->with("info", "A ordem $ordem->codigo já encontra-se excluída");
        }


        $situacoesPermitidas = [
            'encerrada',
            'cancelada',
        ];

        if (!in_array($ordem->situacao, $situacoesPermitidas)) {
            return redirect()->back()->with("info", "Apenas ordens encerradas ou canceladas podem ser excluídas");
        }


        if ($this->request->getMethod() === 'post') {
            $this->ordemModel->delete($ordem->id);

            return redirect()->to(site_url("ordens"))->with("sucesso", "Ordem $ordem->codigo excluída com sucesso!");
        }
        
       

        $data = [
            'titulo' => "Excluíndo a ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];

        return view('Ordens/excluir', $data);
    }

    public function desfazerExclusao(string $codigo = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->deletado_em == null) {
            return redirect()->back()->with('info', "Apenas ordens excluídos podem ser recuperadas");
        }


        $ordem->deletado_em = null;
        $this->ordemModel->protect(false)->save($ordem);

        return redirect()->back()->with('sucesso', "Ordem $ordem->codigo recuperada com sucesso!");
    }

    //-----------Métodos privados---------//

    public function finalizaCadastroDaOrdem(object $ordem) : void
    {
        $ordemAbertura = [
            'ordem_id' => $this->ordemModel->getInsertID(),
            'usuario_abertura_id' => usuario_logado()->id
        ];

        $this->ordemResponsavelModel->insert($ordemAbertura);


        $ordem->cliente = $this->clienteModel->select('nome, email')->find($ordem->cliente_id);

        // Serão usados na view de email
        $ordem->situacao = 'aberta';
        $ordem->criado_em = date('Y/m/d H:i');
        
        // Enviamos o e-mail para o cliente com o conteúdo da ordem
        $this->enviaOrdemEmAndamentoParaCliente($ordem);
    }


    public function enviaOrdemEmAndamentoParaCliente(object $ordem) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem');

        if (isset($ordem->cliente)) {
            $emailCliente = $ordem->cliente->email;
        } else {
            $emailCliente = $ordem->email;
        }

        $email->setTo($emailCliente);
   
        $email->setSubject("Ordem de serviço $ordem->codigo em andamento");

        $data = [
            'ordem' => $ordem
        ];

        $mensagem = view('Ordens/ordem_andamento_email', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    public function enviaOrdemEncerradaParaCliente(object $ordem) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem');

        if (isset($ordem->cliente)) {
            $emailCliente = $ordem->cliente->email;
        } else {
            $emailCliente = $ordem->email;
        }

        $email->setTo($emailCliente);


        if (isset($ordem->transacao)) {
            $tituloEmail = "Ordem de serviço $ordem->codigo encerrada com Boleto Bancário";
        } else {
            $tituloEmail = "Ordem de serviço $ordem->codigo encerrada";
        }


   
        $email->setSubject($tituloEmail);

        $data = [
            'ordem' => $ordem
        ];

        $mensagem = view('Ordens/ordem_encerrada_email', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    /**
     * Método que recupera o usuário
     *
     * @param integer $id
     * @return Exceptions|object
     */
    private function buscaUsuarioOu404(int $usuario_responsavel_id = null)
    {
        if (!$usuario_responsavel_id || !$usuarioResponsavel = $this->usuarioModel->select('id, nome')->where('ativo', true)->find($usuario_responsavel_id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o usuário $usuario_responsavel_id");
        }

        return $usuarioResponsavel;
    }


    private function ordemTemResponsavel(int $ordem_id) : bool
    {
        if ($this->ordemResponsavelModel->where('ordem_id', $ordem_id)->where('usuario_responsavel_id', null)->first()) {
            return false;
        }

        return true;
    }


    private function defineMensagensDesconto(string $valor_desconto)
    {
        $descontoBoleto = env('gerenciaNetDesconto') / 100 . '%';

        $descontoAdicionado = "R$ ". number_format($valor_desconto, 2);

        session()->setFlashdata('sucesso', "Desconto de $descontoAdicionado inserido com sucesso!");

        $usuarioLogado = usuario_logado()->nome;

        session()->setFlashdata('info', "<b>$usuarioLogado</b>,  se esta ordem for encerrada com <b>Boleto Bancário</b>, prevalecerá o valor de desconto de <b>$descontoBoleto</b> para esse método de pagamento.");
    }
}
