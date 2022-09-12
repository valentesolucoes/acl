<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Traits\ValidacoesTrait;

use App\Entities\Fornecedor;

class Fornecedores extends BaseController
{

    use ValidacoesTrait;


    private $fornecedorModel;
    private $fornecedorNotaFiscalModel;

    public function __construct()
    {
        $this->fornecedorModel = new \App\Models\FornecedorModel();
        $this->fornecedorNotaFiscalModel = new \App\Models\FornecedorNotaFiscalModel();
    }

    public function index()  {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Listando os fornecedores',
        ];

        return view('Fornecedores/index', $data);
    }


    public function recuperaFornecedores()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $atributos = [
            'id',
            'razao',
            'cnpj',
            'telefone',
            'ativo',
            'deletado_em'
        ];

        $fornecedores = $this->fornecedorModel->select($atributos)
                                       ->withDeleted(true)
                                       ->orderBy('id', 'DESC')
                                       ->findAll();

        // Receberá o array de objetos de fornecedores
        $data = [];

        foreach ($fornecedores as $fornecedor) {
            $data[] = [
                'razao' => anchor("fornecedores/exibir/$fornecedor->id", esc($fornecedor->razao), 'title="Exibir fornecedor '.esc($fornecedor->razao).' "'),
                'cnpj' => esc($fornecedor->cnpj),
                'telefone' => esc($fornecedor->telefone),
                'ativo' => $fornecedor->exibeSituacao(),
            ];
        }


        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('criar-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $fornecedor = new Fornecedor();

        $data = [
            'titulo' => "Cadastrar novo fornecedor",
            'fornecedor' => $fornecedor,
        ];

        
        return view('Fornecedores/criar', $data);
    }

    public function cadastrar(){

        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();



        if(session()->get('blockCep') === true){

            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];

            return $this->response->setJSON($retorno);

        }


        // Recupero o post da requisição
        $post = $this->request->getPost();


        $fornecedor = new Fornecedor($post);

        if ($this->fornecedorModel->save($fornecedor)) {

            $btnCriar = anchor("fornecedores/criar", 'Cadastrar novo fornecedor', ['class' => 'btn btn-danger mt-2']);
            
            session()->setFlashdata('sucesso', "Dados salvos com sucesso!<br> $btnCriar");

            $retorno['id'] = $this->fornecedorModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->fornecedorModel->errors();

        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function exibir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $fornecedor = $this->buscaFornecedorOu404($id);

        $data = [
            'titulo' => "Detalhando o fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        
        return view('Fornecedores/exibir', $data);
    }


    public function editar(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $fornecedor = $this->buscaFornecedorOu404($id);

        $data = [
            'titulo' => "Editando o fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        
        return view('Fornecedores/editar', $data);
    }


    public function atualizar(){

        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();



        if(session()->get('blockCep') === true){

            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];

            return $this->response->setJSON($retorno);

        }


        // Recupero o post da requisição
        $post = $this->request->getPost();


        $fornecedor = $this->buscaFornecedorOu404($post['id']);

        $fornecedor->fill($post);

        if($fornecedor->hasChanged() === false){

            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }


        if ($this->fornecedorModel->save($fornecedor)) {

            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
        $retorno['erros_model'] = $this->fornecedorModel->errors();

        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }

    public function excluir(int $id = null){

        if( ! $this->usuarioLogado()->temPermissaoPara('excluir-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $fornecedor = $this->buscaFornecedorOu404($id);

        if($fornecedor->deletado_em != null){

            return redirect()->back()->with('info', "Fornecedor $fornecedor->razao já encontra-se excluído");

        }

        if($this->request->getMethod() === 'post'){

            $this->fornecedorModel->delete($id);

            return redirect()->to(site_url("fornecedores"))->with('sucesso', "Fornecedor $fornecedor->razao excluído com sucesso!");
        }

        $data = [
            'titulo' => "Excluindo o fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        return view('Fornecedores/excluir', $data);

    }

    public function desfazerExclusao(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $fornecedor = $this->buscaFornecedorOu404($id);

        if ($fornecedor->deletado_em === null) {
            return redirect()->back()->with('info', "Apenas forneedores excluídos podem ser recuperados");
        }


        $fornecedor->deletado_em = null;
        $this->fornecedorModel->protect(false)->save($fornecedor);

        return redirect()->back()->with('sucesso', "Fornecedor $fornecedor->razao recuperado com sucesso!");
    }


    public function notas(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $fornecedor = $this->buscaFornecedorOu404($id);

        $fornecedor->notas_fiscais = $this->fornecedorNotaFiscalModel->where('fornecedor_id', $fornecedor->id)->paginate(10);
        
        if($fornecedor->notas_fiscais != null){

            $fornecedor->pager = $this->fornecedorNotaFiscalModel->pager;
        }

    
        $data = [
            'titulo' => "Gerenciado as notas fiscais do fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        
        return view('Fornecedores/notas_fiscais', $data);
    }

    public function cadastrarNotaFiscal(){

        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $post = $this->request->getPost();

        $valorNota = str_replace([',', '.'], '', $post['valor_nota']);

        if($valorNota < 1){

            $retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
            $retorno['erros_model'] = ['valor_nota' => 'O valor da nota deve ser maior que zero'];
            return $this->response->setJSON($retorno);
        }


        $validacao = service('validation');

        $regras = [
            'valor_nota' => 'required',
            'data_emissao' => 'required',
            'nota_fiscal' => 'uploaded[nota_fiscal]|max_size[nota_fiscal,1024]|ext_in[nota_fiscal,pdf]',
            'descricao_itens' => 'required',
        ];

        $mensagens = [   // Errors
            'nota_fiscal' => [
                'uploaded' => 'Por favor escolha uma nota fiscal',
                'max_size' => 'Por favor escolha uma nota fiscal de no máximo 1024mb',
                'ext_in'   => 'Por favor escolha uma nota fiscal que seja em PDF',
            ],
        ];

        $validacao->setRules($regras, $mensagens);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $fornecedor = $this->buscaFornecedorOu404($post['id']);

        $notaFiscal = $this->request->getFile('nota_fiscal');

       $notaFiscal->store('fornecedores/notas_fiscais');

        $nota = [
            'fornecedor_id' => $fornecedor->id,
            'nota_fiscal' => $notaFiscal->getName(),
            'descricao_itens' => $post['descricao_itens'],
            'valor_nota' => str_replace(',', '', $post['valor_nota']),
            'data_emissao' => $post['data_emissao']
        ];

        $this->fornecedorNotaFiscalModel->insert($nota);

        session()->setFlashdata('sucesso', 'Nota fiscal cadastrada com sucesso!');

        return $this->response->setJSON($retorno);
    }


    public function exibirNota(string $nota = null){

        if($nota === null){

            return redirect()->to(site_url("fornecedores"))->with('atencao', "Não encontramos a nota fiscal $nota");
        }


        $this->exibeArquivo('fornecedores/notas_fiscais', $nota);

    }

    public function removeNota(string $nota_fiscal = null){

        if($this->request->getMethod() === 'post'){

            if( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')){

                return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
            }

            $objetoNota = $this->buscaNotaFiscalOu404($nota_fiscal);

            $this->fornecedorNotaFiscalModel->delete($objetoNota->id);

            $caminhoNotaFiscal = WRITEPATH . "uploads/fornecedores/notas_fiscais/$nota_fiscal";

            if(is_file($caminhoNotaFiscal)){

                unlink($caminhoNotaFiscal);

            }

            return redirect()->back()->with("sucesso", "Nota fiscal removida com sucessio!");


        }

        // Não é POST
        return redirect()->back();


    }



    public function consultaCep(){

        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        $cep = $this->request->getGet('cep');

        
        return $this->response->setJSON($this->consultaViaCep($cep));

    }

    /**
     * Método que recupera o fornecedor
     *
     * @param integer $id
     * @return Exceptions|object
     */
    private function buscaFornecedorOu404(int $id = null)
    {
        if (!$id || !$fornecedor = $this->fornecedorModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o fornecedor $id");
        }

        return $fornecedor;
    }
    /**
     * Método que recupera a nota fiscal fornecedor
     *
     * @param string $nota_fiscal
     * @return Exceptions|object
     */
    private function buscaNotaFiscalOu404(string $nota_fiscal = null)
    {

        if (!$nota_fiscal || !$objetoNota = $this->fornecedorNotaFiscalModel->where('nota_fiscal', $nota_fiscal)->first()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a nota fiscal $nota_fiscal");
        }

        return $objetoNota;
    }
}
