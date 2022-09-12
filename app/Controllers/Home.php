<?php

namespace App\Controllers;

class Home extends BaseController
{

    private $ordemModel;
    private $usuarioModel;
    private $ordemItemModel;
    private $clienteModel;
    private $fornecedorModel;
    private $itemModel;


    public function __construct()
    {
        $this->ordemModel = new \App\Models\OrdemModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->ordemItemModel = new \App\Models\OrdemItemModel();
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->fornecedorModel = new \App\Models\FornecedorModel();
        $this->itemModel = new \App\Models\ItemModel();
    }

    public function index()
    {

        $data = [
            'titulo' => 'Home'
        ];

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-home')){

            return view('Home/index_simples', $data);
            
        }

        $data['totalClientes'] = $this->clienteModel->countAllResults();
        $data['totalFornecedores'] = $this->fornecedorModel->countAllResults();
        $data['totalItens'] = $this->itemModel->countAllResults();
        $data['totalOrdensEncerradas'] = $this->ordemModel->where('situacao', 'encerrada')->countAllResults();

        $data = $this->preparaDadosGraficosParaView($data);

        

        return view('Home/index', $data);
    }


    private function preparaDadosGraficosParaView(array $data) : array {

        $dadosClientes = $this->ordemModel->recuperaClientesMaisAssiduos(date('Y'));

        
        if( ! empty($dadosClientes)){

            $data['dadosClientes'] = $dadosClientes;
        }


        $dadosDesempenho = $this->usuarioModel->recuperaAtendentesGrafico(date('Y'));

        
        if( ! empty($dadosDesempenho)){

            $data['dadosDesempenho'] = $dadosDesempenho;
        }


        $produtosMaisVendidos = $this->ordemItemModel->recuperaItensMaisVendidosGraficos(date('Y'), 'produto', 10);

        $data['produtosMaisVendidos'] = $produtosMaisVendidos;


        $servicosMaisVendidos = $this->ordemItemModel->recuperaItensMaisVendidosGraficos(date('Y'), 'serviço', 10);


        $data['servicosMaisVendidos'] = $servicosMaisVendidos;
        

        $atendimentosPorMes = $this->ordemModel->recuperaOrdensPorMesGrafico(date('Y'));

        $data['atendimentosPorMes'] = $atendimentosPorMes;

        
        return $data;

    }

}
