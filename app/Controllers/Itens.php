<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Entities\Item;

class Itens extends BaseController
{
    private $itemModel;
    private $itemHistoricoModel;
    private $itemImagemModel;

    public function __construct()
    {
        $this->itemModel = new \App\Models\ItemModel();
        $this->itemHistoricoModel = new \App\Models\ItemHistoricoModel();
        $this->itemImagemModel = new \App\Models\ItemImagemModel();
    }

    public function index()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Listando os itens da base de dados'
        ];

        return view('Itens/index', $data);
    }

    public function recuperaItens()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $atributos = [
            'id',
            'nome',
            'tipo',
            'estoque',
            'preco_venda',
            'ativo',
            'deletado_em'
        ];

    
        $itens = $this->itemModel
                      ->select($atributos)
                      ->withDeleted(true)
                      ->orderBy('id', 'DESC')
                      ->findAll();


        $data = [];


        foreach ($itens as $item) {
            $data[] = [
                'nome' => anchor("itens/exibir/$item->id", esc($item->nome), 'title="Exibir item '.esc($item->nome).' "'),
                'tipo' => $item->exibeTipo(),
                'estoque' => $item->exibeEstoque(),
                'preco_venda' => 'R$&nbsp;'.$item->preco_venda,
                'situacao' => $item->exibeSituacao(),
            ];
        }


        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }


    public function exibir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = $this->buscaItemOu404($id);


        // Recuperamos o histórico do item
        $this->defineHistoricoItem($item);


        // Defino a imagem do item tipo produto
        if($item->tipo === "produto"){

            $itemImagem = $this->itemImagemModel->select('imagem')->where('item_id', $item->id)->first();

            if($itemImagem !== null){

                $item->imagem = $itemImagem->imagem;

            }

        }
        
    
        $data = [
            'titulo' => 'Detalhando o item '. $item->nome. ' '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/exibir', $data);
    }


    public function criar()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('criar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = new Item();
    
        $data = [
            'titulo' => 'Cadastrando novo item',
            'item' => $item,
        ];

        return view('Itens/criar', $data);
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


        $item = new Item($post);

        $item->codigo_interno = $this->itemModel->geraCodigoInternoItem();

        if ($item->tipo === 'produto') {
            if ($item->marca == "" || $item->marca === null) {
                $retorno['erro'] = 'Verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['estoque' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar a marca do mesmo'];

                return $this->response->setJSON($retorno);
            }

            if ($item->modelo == "" || $item->modelo === null) {
                $retorno['erro'] = 'Verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['estoque' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar o modelo do mesmo'];

                return $this->response->setJSON($retorno);
            }


            if ($item->estoque == "") {
                $retorno['erro'] = 'Verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['estoque' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar a quantidade em estoque'];

                return $this->response->setJSON($retorno);
            }


            $precoCusto = str_replace([',', '.'], '', $item->preco_custo);
            $precoVenda = str_replace([',', '.'], '', $item->preco_venda);


            if ($precoCusto > $precoVenda) {
                $retorno['erro'] = 'Verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['estoque' => 'O preço de venda <b class="text-white">não pode ser menor</b> do que o preço de custo'];

                return $this->response->setJSON($retorno);
            }
        }

        if ($this->itemModel->save($item)) {
            $btnCriar = anchor("itens/criar", 'Cadastrar novo item', ['class' => 'btn btn-danger mt-2']);
            
            session()->setFlashdata('sucesso', "Dados salvos com sucesso!<br> $btnCriar");

            $retorno['id'] = $this->itemModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->itemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }


    public function editar(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = $this->buscaItemOu404($id);
    
        $data = [
            'titulo' => 'Editando o item '. $item->nome. ' '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/editar', $data);
    }


    public function codigoBarras(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = $this->buscaItemOu404($id);

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();

        $item->codigo_barras = $generator->getBarcode($item->codigo_interno, $generator::TYPE_CODE_128, 3, 80);


        $data = [
            'titulo' => 'Código de barras do Item '. $item->exibeTipo(),
            'item' => $item,
        ];
        
        return view('Itens/codigo_barras', $data);
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


        $item = $this->buscaItemOu404($post['id']);


        $item->fill($post);


        if ($item->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }


        if ($item->tipo === 'produto') {
            if ($item->estoque == "") {
                $retorno['erro'] = 'Verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['estoque' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar a quantidade em estoque'];

                return $this->response->setJSON($retorno);
            }


            $precoCusto = str_replace([',', '.'], '', $item->preco_custo);
            $precoVenda = str_replace([',', '.'], '', $item->preco_venda);


            if ($precoCusto > $precoVenda) {
                $retorno['erro'] = 'Verifique os erros abaixo e tente novamente';
                $retorno['erros_model'] = ['estoque' => 'O preço de venda <b class="text-white">não pode ser menor</b> do que o preço de custo'];

                return $this->response->setJSON($retorno);
            }
        }

        if ($this->itemModel->save($item)) {

            // Fazemos o store do histórico do item
            $this->insereHistoricoItem($item, 'Atualização');

            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->itemModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);
    }

    public function editarImagem(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = $this->buscaItemOu404($id);

        if ($item->tipo === 'serviço') {
            return redirect()->back()->with('info', "Você poderá alterar as imagens apenas de um item do tipo Produto");
        }

        $item->imagens = $this->itemImagemModel->where('item_id', $item->id)->findAll();


        $data = [
            'titulo' => 'Gerenciando as imagens do item '. $item->nome. ' '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/editar_imagem', $data);
    }

    public function upload()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');


        $regras = [
            'imagens' => 'uploaded[imagens]|max_size[imagens,1024]|ext_in[imagens,png,jpg,jpeg,webp]',
        ];

        $mensagens = [   // Errors
            'imagens' => [
                'uploaded' => 'Por favor escolha uma imagem ou mais imagens',
                'max_size' => 'Por favor escolha uma imagem de no máximo 1024',
                'ext_in'   => 'Por favor escolha uma imagem png, jpg, jpeg, ou webp',
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

        // Validamos a existência do item
        $item = $this->buscaItemOu404($post['id']);


        $resultadoTotalImagens = $this->defineQuantidadeImagens($item->id);
        
        if ($resultadoTotalImagens['totalImagens'] > 10) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['total_imagens' => "O produto pode ter no máximo 10 imagens. Ele já possui ".$resultadoTotalImagens['existentes']];

            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        // É plural agora
        $imagens = $this->request->getFiles('imagens');

        // Primeiro foreach apenas para validar largura e altura mínima das imagens
        foreach ($imagens['imagens'] as $imagem) {
            list($largura, $altura) = getimagesize($imagem->getPathName());

            if ($largura < "400" || $altura < "400") {
                $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
                $retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 400 x 400 pixels'];
    
                // Retorno para o ajax request
                return $this->response->setJSON($retorno);
            }
        }


        // Receberá as imagens para o insertBatch
        $arrayImagens = [];

        foreach ($imagens['imagens'] as $imagem) {
            $caminhoImagem = $imagem->store('itens');

            $caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";

            // Redimensionamos a imagem para 400 x 400 e para ficar no centro
            // E fazemos a marca dágua
            $this->manipulaImagem($caminhoImagem, $item->id);

            array_push($arrayImagens, [
                'item_id' => $item->id,
                'imagem' => $imagem->getName(),
            ]);
        } // fim segundo foreach

        $this->itemImagemModel->insertBatch($arrayImagens);

        session()->setFlashdata('sucesso', 'Imagens salvas com sucesso!');

        return $this->response->setJSON($retorno);
    }


    public function imagem(string $imagem = null)
    {
        if ($imagem != null) {
            $this->exibeArquivo('itens', $imagem);
        }
    }


    public function removeImagem(string $imagem = null)
    {
        if ($this->request->getMethod() === 'post') {

            if( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')){

                return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
            }

            $objetoImagem = $this->buscaImagemOu404($imagem);

            $this->itemImagemModel->delete($objetoImagem->id);

            $caminhoImagem = WRITEPATH . "uploads/itens/$imagem";

            if (is_file($caminhoImagem)) {
                unlink($caminhoImagem);
            }

            return redirect()->back()->with("sucesso", "Imagem removida com sucesso!");
        }

        // Não é POST
        return redirect()->back();
    }


    public function excluir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('excluir-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = $this->buscaItemOu404($id);

        if ($item->deletado_em != null) {
            return redirect()->back()->with('info', "Item $item->nome já encontra-se excluído");
        }

        if ($this->request->getMethod() === 'post') {


            $this->itemModel->delete($item->id);

            $this->insereHistoricoItem($item, "Exclusão");

            if($item->tipo === "produto"){

                $this->removeTodasImagensDoItem($item->id);

            }

            return redirect()->to(site_url("itens"))->with('sucesso', "Item $item->nome excluído com sucesso!");
        }

        $data = [
            'titulo' => 'Excluindo o item '. $item->nome. ' '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/excluir', $data);
    }

    public function desfazerExclusao(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $item = $this->buscaItemOu404($id);

        if ($item->deletado_em === null) {
            return redirect()->back()->with('info', "Apenas itens excluídos podem ser recuperados");
        }


        $item->deletado_em = null;
        $this->itemModel->protect(false)->save($item);

        $this->insereHistoricoItem($item, "Recuperação");

        return redirect()->back()->with('sucesso', "Item $item->nome recuperado com sucesso!");
    }


    /*---------------------------Métodos privados--------------------------*/

    /**
     * Método que recupera o item
     *
     * @param integer $id
     * @return Exceptions|object
     */
    private function buscaItemOu404(int $id = null)
    {
        if (!$id || !$item = $this->itemModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o item $id");
        }

        return $item;
    }

    /**
     * Método que recupera a imagem do item
     *
     * @param string $imagem
     * @return Exceptions|object
     */
    private function buscaImagemOu404(string $imagem = null)
    {
        if (!$imagem || !$objetoImagem = $this->itemImagemModel->where('imagem', $imagem)->first()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a imagem $imagem");
        }

        return $objetoImagem;
    }



    /**
     * Método que define o histórico de alterações do item
     *
     * @param object $item
     * @return object
     */
    private function defineHistoricoItem(object $item) : object
    {
        
        $historico = $this->itemHistoricoModel->recuperaHistoricoItem($item->id);
                          
        if ($historico != null) {
            foreach ($historico as $key => $hist) {
                $historico[$key]['atributos_alterados'] = unserialize($hist['atributos_alterados']);
            }

            $item->historico = $historico;
        }

        return $item;
    }



    /**
     * Método que insere o histórico de alterações do item
     *
     * @param object $item
     * @return void
     */
    private function insereHistoricoItem(object $item, string $acao) : void
    {
        $historico = [
            'usuario_id'=> usuario_logado()->id,
            'item_id' => $item->id,
            'acao' => $acao,
            'atributos_alterados' => $item->recuperaAtributosAlterados()
        ];


        $this->itemHistoricoModel->insert($historico);
    }


    private function manipulaImagem(string $caminhoImagem, int $item_id)
    {
        service('image')
            ->withFile($caminhoImagem)
            ->fit(400, 400, 'center')
            ->save($caminhoImagem);


        $anoAtual = date('Y');

        // Adicionar uma marca d'água de texto
        \Config\Services::image('imagick')
            ->withFile($caminhoImagem)
            ->text("Ordem $anoAtual - Produto-ID $item_id", [
                'color'      => '#fff',
                'opacity'    => 0.5,
                'withShadow' => false,
                'hAlign'     => 'center',
                'vAlign'     => 'bottom',
                'fontSize'   => 10
            ])
            ->save($caminhoImagem);
    }


    private function defineQuantidadeImagens(int $item_id) : array
    {

        // Recupero as imagens que o item já possui
        $existentes = $this->itemImagemModel->where('item_id', $item_id)->countAllResults();

                
        // Contamos o número de imagens que estão vindo no post
        $quantidadeImagensPost = count(array_filter($_FILES['imagens']['name']));


        $retorno = [
            'existentes' => $existentes,
            'totalImagens' => $existentes + $quantidadeImagensPost,
        ];


        return $retorno;
    }


    private function removeTodasImagensDoItem(int $item_id) : void
    {

        // Recupero as imagens que o item pode ou não possuir
        $itensImagens = $this->itemImagemModel->where('item_id', $item_id)->findAll();

        if(empty($itensImagens) === false){

            $this->itemImagemModel->where('item_id', $item_id)->delete();

            foreach($itensImagens as $imagem){

                $caminhoImagem = WRITEPATH . "uploads/itens/$imagem->imagem";

                if(is_file($caminhoImagem)){

                    unlink($caminhoImagem);
                }

            }

        }

    }
}
