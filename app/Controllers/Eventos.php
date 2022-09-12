<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Eventos extends BaseController
{

	private $eventoModel;

	public function __construct()
	{
		$this->eventoModel = new \App\Models\EventoModel();
	}

	public function index()
	{

		if( ! $this->usuarioLogado()->temPermissaoPara('listar-eventos')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

		$data = [
			'titulo' => 'Listando os eventos'
		];

		return view('Eventos/index', $data);
	}

	public function eventos(){

		if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

		$dataGet = $this->request->getGet();

		$retorno = $this->eventoModel->recuperaEventos($dataGet);

		return $this->response->setJSON($retorno);
	}

	public function cadastrar(){

		if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

		$dataGet = $this->request->getGet();

		if($this->eventoModel->insert($dataGet)){

			$retorno['evento'] = $this->eventoModel->getInsertID();

			return $this->response->setJSON($retorno);
		}

		$retorno['erro'] = $this->eventoModel->errors();
		return $this->response->setJSON($retorno);

	}


	public function atualizar($id = null){


		if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

		$dataGet = $this->request->getGet();

		if($this->eventoModel->update($id, $dataGet)){


			$retorno['evento'] = $id;

			return $this->response->setJSON($retorno);

		}

		$retorno['erro'] = $this->eventoModel->errors();
		return $this->response->setJSON($retorno);

	}


	public function excluir(){

		if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

		$dataGet = $this->request->getGet();

		if($this->eventoModel->delete($dataGet['id'])){

			$retorno['evento'] = $dataGet['id'];

			return $this->response->setJSON($retorno);

		}

		$retorno['erro'] = $this->eventoModel->errors();
		return $this->response->setJSON($retorno);


	}
}
