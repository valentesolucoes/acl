<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\FormaPagamento;

class FormasPagamentos extends BaseController
{
    private $formaPagamentoModel;
  

    public function __construct()
    {
        $this->formaPagamentoModel = new \App\Models\FormaPagamentoModel();
    }

    public function index()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-formas')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Listando as formas de pagamentos',
        ];

        return view('FormasPagamentos/index', $data);
    }


    public function recuperaFormas()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        $formas = $this->formaPagamentoModel->findAll();


        // Receberá o array de objetos de fornecedores
        $data = [];

        foreach ($formas as $forma) {
            $data[] = [
                'nome' => anchor("formas/exibir/$forma->id", esc($forma->nome), 'title="Exibir a forma de pagamento '.esc($forma->nome).' "'),
                'descricao' => esc($forma->descricao),
                'criado_em' => esc($forma->criado_em->humanize()),
                'situacao' => $forma->exibeSituacao(),
            ];
        }


        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }


    public function criar()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('criar-formas')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $forma = new FormaPagamento();

        $data = [
            'titulo' => 'Criando nova forma de pagamento',
            'forma' => $forma,
        ];

        return view('FormasPagamentos/criar', $data);
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


		$forma = new FormaPagamento($post);

	
		if ($this->formaPagamentoModel->save($forma)) {
            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

			$retorno['id'] = $this->formaPagamentoModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->formaPagamentoModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);

	}

    public function exibir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('listar-formas')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $forma = $this->buscaFormaOu404($id);

        $data = [
            'titulo' => 'Detalhando a forma de pagamento '.esc($forma->nome),
            'forma' => $forma,
        ];

        return view('FormasPagamentos/exibir', $data);
    }


    public function editar(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('editar-formas')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $forma = $this->buscaFormaOu404($id);

		if($forma->id < 3){

			return redirect()->to(site_url("formas/exibir/$forma->id"))->with("info", "A forma de pagamento <b>$forma->nome</b> não pode ser editada ou excluída.");

		}


        $data = [
            'titulo' => 'Editando a forma de pagamento '.esc($forma->nome),
            'forma' => $forma,
        ];

        return view('FormasPagamentos/editar', $data);
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


        // Validamos a existência do grupo
        $forma = $this->buscaFormaOu404($post['id']);


		if($forma->id < 3){

			// Retornamos os erros de validação
			$retorno['erro'] = 'Por favor verifique os erros abaixo e tente novamente';
			$retorno['erros_model'] = ['forma' => "A forma de pagamento <b class='text-white'>$forma->nome</b> não pode ser editada ou excluída."];
	
			// Retorno para o ajax request
			return $this->response->setJSON($retorno);

		}


		$forma->fill($post);

		if ($forma->hasChanged() === false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }


		if ($this->formaPagamentoModel->save($forma)) {
            session()->setFlashdata('sucesso', 'Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        // Retornamos os erros de validação
        $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
        $retorno['erros_model'] = $this->formaPagamentoModel->errors();


        // Retorno para o ajax request
        return $this->response->setJSON($retorno);

	}


	public function excluir(int $id = null)
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('excluir-formas')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $forma = $this->buscaFormaOu404($id);

		if($forma->id < 3){

			return redirect()->to(site_url("formas/exibir/$forma->id"))->with("atencao", "A forma de pagamento <b>$forma->nome</b> não pode ser editada ou excluída.");

		}


		if($this->request->getMethod() === 'post'){

			if($forma->id < 3){

				return redirect()->to(site_url("formas/exibir/$forma->id"))->with("atencao", "A forma de pagamento <b>$forma->nome</b> não pode ser editada ou excluída.");
	
			}

			$this->formaPagamentoModel->delete($forma->id);

			return redirect()->to(site_url("formas"))->with("sucesso", "A forma de pagamento <b>$forma->nome</b> excluída com sucesso!.");

		}


        $data = [
            'titulo' => 'Excluindo a forma de pagamento '.esc($forma->nome),
            'forma' => $forma,
        ];

        return view('FormasPagamentos/excluir', $data);
    }



    //--------------Método privados------//
    /**
	 * Método que recupera a forma de pagamento
	 *
	 * @param integer $id
	 * @return Exceptions|object
	 */
    private function buscaFormaOu404(int $id = null)
    {
        if (!$id || !$forma = $this->formaPagamentoModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a forma de pagamento $id");
        }

        return $forma;
    }
}
