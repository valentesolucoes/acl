<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use Dompdf\Dompdf;

class Relatorios extends BaseController
{
    private $itemModel;
    private $ordemItemModel;
    private $ordemModel;
    private $contaPagarModel;
    private $usuarioModel;

    public function __construct()
    {
        $this->itemModel = new \App\Models\ItemModel();
        $this->ordemItemModel = new \App\Models\OrdemItemModel();
        $this->ordemModel = new \App\Models\OrdemModel();
        $this->contaPagarModel = new \App\Models\ContaPagarModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
    }

    public function itens()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Relatórios de itens'
        ];

        return view('Relatorios/Itens/itens', $data);
    }


    public function gerarRelatorioProdutosEstoqueZerado()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        // COLOCAR ACL AQUI

        $produtos = $this->itemModel->where('tipo', 'produto')->where('controla_estoque', true)->where('estoque <', 1)->findAll();

        $data = [
            'titulo' => 'Relatório de produtos com estoque zerado ou negativo, gerado em: '. date('d/m/Y H:i'),
            'produtos' => $produtos
        ];


        $nomeArquivo = 'relatorio-produtos-com-estoque-zerado-negativo.pdf';


        $dompdf = new Dompdf();

        $dompdf->loadHtml(view('Relatorios/Itens/relatorio_estoque_zerado', $data));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($nomeArquivo, ['Attachment' => false]);

        unset($dompdf);
        unset($dompdf);

        exit();
    }


    public function itensMaisVendidos()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');

        $regras = [
            'tipo' => 'required|in_list[produto,serviço]',
            'data_inicial' => 'required',
            'data_final' => 'required',
        ];


        $validacao->setRules($regras);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $post = $this->request->getPost();

        // 1632686640 | 1632686640
        $dataInicial = strtotime($post['data_inicial']);
        $dataFinal = strtotime($post['data_final']);

        if ($dataInicial > $dataFinal) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['datas' => 'A Data Inicial não pode ser menor que a Data Final'];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $itens = $this->ordemItemModel->recuperaItensMaisVendidos($post['tipo'], $post['data_inicial'], $post['data_final']);

        $retorno['redirect'] = 'relatorios/itens-mais-vendidos';

        session()->set('itens', $itens);
        session()->set('post', $post);

        return $this->response->setJSON($retorno);
    }


    public function gerarRelatorioItensMaisVendidos()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        // COLOCAR ACL AQUI

        if (! session()->has('itens') || ! session()->has('post')) {
            return redirect()->to(site_url('relatorios/itens'))->with('atencao', 'Não foi possível gerar o relatório. Tente novamente');
        }

        $itens = session()->get('itens');
        $post = session()->get('post');


        $data = [
            'titulo' => 'Relatório de '. plural(ucfirst($post['tipo'])) .' mais vendidos, gerado em: '. date('d/m/Y H:i'),
            'itens' => $itens,
            'periodo' => 'Compreendendo o período entre ' .date('d/m/Y H:i', strtotime($post['data_inicial'])). ' e ' . date('d/m/Y H:i', strtotime($post['data_final'])),
        ];


        $nomeArquivo = 'relatorio-'.$post['tipo'].'-mais-vendidos.pdf';

        $dompdf = new Dompdf();

        $dompdf->loadHtml(view('Relatorios/Itens/relatorio_itens_mais_vendidos', $data));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($nomeArquivo, ['Attachment' => false]);

        unset($data);
        unset($dompdf);

        exit();
    }


    //--------- Ordens--------///

    public function ordens()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Relatórios de ordens de serviços'
        ];

        return view('Relatorios/Ordens/ordens', $data);
    }


    public function gerarRelatorioOdens()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');

        $regras = [
            'situacao' => 'required|in_list[aberta,encerrada,excluida,aguardando,nao_pago,cancelada,boleto]',
            'data_inicial' => 'required',
            'data_final' => 'required',
        ];


        $validacao->setRules($regras);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $post = $this->request->getPost();

        // 1632686640 | 1632686640
        $dataInicial = strtotime($post['data_inicial']);
        $dataFinal = strtotime($post['data_final']);

        if ($dataInicial > $dataFinal) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['datas' => 'A Data Inicial não pode ser menor que a Data Final'];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        session()->remove('ordens');
        session()->remove('post');


        if ($post['situacao'] === 'aberta') {
            $ordens = $this->ordemModel->recuperaOrdensPelaSituacao($post['situacao'], $post['data_inicial'], $post['data_final']);


            $retorno['redirect'] = 'relatorios/ordens-abertas';


            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_abertas';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }

        if ($post['situacao'] === 'encerrada') {
            $ordens = $this->ordemModel->recuperaOrdensPelaSituacao($post['situacao'], $post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/ordens-encerradas';

            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_nao_abertas';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }

        if ($post['situacao'] === 'excluida') {
            $ordens = $this->ordemModel->recuperaOrdensExcluidas($post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/ordens-excluidas';

            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_nao_abertas';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }

        if ($post['situacao'] === 'aguardando') {
            $ordens = $this->ordemModel->recuperaOrdensPelaSituacao($post['situacao'], $post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/ordens-aguardando-pagamento';

            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_nao_abertas';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }

        if ($post['situacao'] === 'cancelada') {
            $ordens = $this->ordemModel->recuperaOrdensPelaSituacao($post['situacao'], $post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/ordens-canceladas';

            $post['situacao'] = 'Com Boletos cancelados';

            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_nao_abertas';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }


        if ($post['situacao'] === 'nao_pago') {
            $ordens = $this->ordemModel->recuperaOrdensPelaSituacao($post['situacao'], $post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/ordens-nao-pagas';

            $post['situacao'] = 'Não paga';

            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_nao_abertas';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }


        if ($post['situacao'] === 'boleto') {
            $ordens = $this->ordemModel->recuperaOrdensComBoleto($post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/ordens-com-boleto';

            $post['situacao'] = 'Ordens processadas com boleto';

            // Nome da view a ser renderizada
            $post['viewRelatorio'] = 'relatorio_ordens_com_boleto';

            session()->set('ordens', $ordens);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }
    }

    public function exibeRelatorioOrdens()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        if (! session()->has('ordens') || ! session()->has('post')) {
            return redirect()->to(site_url('relatorios/ordens'))->with('atencao', 'Não foi possível gerar o relatório. Tente novamente');
        }

        $ordens = session()->get('ordens');
        $post = session()->get('post');


        $data = [
            'titulo' => 'Relatório de ordens '. plural(ucfirst($post['situacao'])) .' gerado em: '. date('d/m/Y H:i'),
            'ordens' => $ordens,
            'periodo' => 'Compreendendo o período entre ' .date('d/m/Y H:i', strtotime($post['data_inicial'])). ' e ' . date('d/m/Y H:i', strtotime($post['data_final'])),
        ];


        $viewRelatorio = $post['viewRelatorio'];

        $view = view("Relatorios/Ordens/$viewRelatorio", $data);

        $nomeArquivo = 'relatorio-ordens'.$post['situacao'].'.pdf';

        $dompdf = new Dompdf();

        $dompdf->loadHtml($view);
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();
        $dompdf->stream($nomeArquivo, ['Attachment' => false]);

        unset($data);
        unset($dompdf);

        exit();
    }


    
    //--------- Contas--------///

    public function contas()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Relatórios de contas de fornecedores'
        ];

        return view('Relatorios/Contas/contas', $data);
    }


    public function gerarRelatorioContas()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');

        $regras = [
            'situacao' => 'required|in_list[abertas,pagas,vencidas]',
            'data_inicial' => 'required',
            'data_final' => 'required',
        ];


        $post = $this->request->getPost();

        if ($post['situacao'] === 'vencidas') {
            unset($regras['data_inicial']);
            unset($regras['data_final']);
        }

        $validacao->setRules($regras);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }



        if (isset($post['data_inicial']) && isset($post['data_final'])) {
            $dataInicial = strtotime($post['data_inicial']);
            $dataFinal = strtotime($post['data_final']);

            if ($dataInicial > $dataFinal) {
                $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
                $retorno['erros_model'] = ['datas' => 'A Data Inicial não pode ser menor que a Data Final'];


                // Retorno para o ajax request
                return $this->response->setJSON($retorno);
            }
        }


        session()->remove('contas');
        session()->remove('post');




        if ($post['situacao'] === 'pagas') {
            $situacao = 1;

            $contas = $this->contaPagarModel->recuperaContasPagasOuAbertas($post['data_inicial'], $post['data_final'], $situacao);

            $retorno['redirect'] = 'relatorios/contas-pagas';

            session()->set('contas', $contas);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }


        if ($post['situacao'] === 'abertas') {
            $situacao = 0;

            $contas = $this->contaPagarModel->recuperaContasPagasOuAbertas($post['data_inicial'], $post['data_final'], $situacao);

            // Removo do array contas as que estão vencidas.... facultativo
            if (! empty($contas)) {
                foreach ($contas as $key => $conta) {
                    if ($conta->data_vencimento < date('Y-m-d')) {
                        unset($contas[$key]);
                    }
                }
            }

            $retorno['redirect'] = 'relatorios/contas-abertas';

            session()->set('contas', $contas);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }

        if ($post['situacao'] === 'vencidas') {
            $situacao = 0;

            $contas = $this->contaPagarModel->recuperaContasVencidas();

            $retorno['redirect'] = 'relatorios/contas-vencidas';

            session()->set('contas', $contas);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }
    }

    public function exibeRelatorioContas()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        if (! session()->has('contas') || ! session()->has('post')) {
            return redirect()->to(site_url('relatorios/contas'))->with('atencao', 'Não foi possível gerar o relatório. Tente novamente');
        }

        $contas = session()->get('contas');
        $post = session()->get('post');


        $data = [
            'titulo' => 'Relatório de contas '. plural(ucfirst($post['situacao'])) .' gerado em: '. date('d/m/Y H:i'),
            'contas' => $contas,
        ];
        

        if (isset($post['data_inicial']) && isset($post['data_final'])) {
            $data['periodo'] = 'Compreendendo o período entre ' .date('d/m/Y H:i', strtotime($post['data_inicial'])). ' e ' . date('d/m/Y H:i', strtotime($post['data_final']));
        }

        $view = view("Relatorios/Contas/relatorio_contas", $data);

        $nomeArquivo = 'relatorio-contas'.$post['situacao'].'.pdf';

        $dompdf = new Dompdf();

        $dompdf->loadHtml($view);
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();
        $dompdf->stream($nomeArquivo, ['Attachment' => false]);

        unset($data);
        unset($dompdf);

        exit();
    }

    //---------------Equipe-------//

    public function equipe()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        $data = [
            'titulo' => 'Relatórios de desempenho da equipe'
        ];

        return view('Relatorios/Equipe/equipe', $data);
    }


    public function gerarRelatorioEquipe()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');

        $regras = [
            'grupo' => 'required|in_list[atendentes,responsaveis]',
            'data_inicial' => 'required',
            'data_final' => 'required',
        ];


        $validacao->setRules($regras);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $post = $this->request->getPost();


        $dataInicial = strtotime($post['data_inicial']);
        $dataFinal = strtotime($post['data_final']);

        if ($dataInicial > $dataFinal) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['datas' => 'A Data Inicial não pode ser menor que a Data Final'];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        session()->remove('usuarios');
        session()->remove('post');

        
        if($post['grupo'] === 'atendentes'){

            $usuarios = $this->usuarioModel->recuperaAtendentesParaRelatorio($post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/desempenho-atendentes';

            $post['grupo'] = 'Atendentes';
            $post['temFinalTitulo'] = false; // Usaremos para compor o título do relatório

            session()->set('usuarios', $usuarios);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);
        }


        if($post['grupo'] === 'responsaveis'){


            $usuarios = $this->usuarioModel->recuperaResponsaveisParaRelatorio($post['data_inicial'], $post['data_final']);

            $retorno['redirect'] = 'relatorios/desempenho-responsaveis';

            $post['grupo'] = 'Responsáves técnicos';
            $post['temFinalTitulo'] = true; // Usaremos para compor o título do relatório

            session()->set('usuarios', $usuarios);
            session()->set('post', $post);

            return $this->response->setJSON($retorno);

        }

    }


    public function exibeRelatorioEquipe()
    {

        if( ! $this->usuarioLogado()->temPermissaoPara('visualizar-relatorios')){

            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.' );
        }

        if (! session()->has('usuarios') || ! session()->has('post')) {
            return redirect()->to(site_url('relatorios/equipe'))->with('atencao', 'Não foi possível gerar o relatório. Tente novamente');
        }

        $usuarios = session()->get('usuarios');
        $post = session()->get('post');


        $finalTitulo = ($post['temFinalTitulo'] === true ? '<br>Sendo computadas as ordens que não estão em aberto' : '');
        $titulo = 'Relatório de desempenho dos '.ucfirst($post['grupo']). ',  gerado em: '. date('d/m/Y H:i') . $finalTitulo;


        $data = [
            'titulo' => $titulo,
            'usuarios' => $usuarios,
        ];
        

        if (isset($post['data_inicial']) && isset($post['data_final'])) {
            $data['periodo'] = 'Compreendendo o período entre ' .date('d/m/Y H:i', strtotime($post['data_inicial'])). ' e ' . date('d/m/Y H:i', strtotime($post['data_final']));
        }

        $view = view("Relatorios/Equipe/relatorio_equipe", $data);

        $nomeArquivo = 'relatorio-desempenho'.$post['grupo'].'.pdf';

        $dompdf = new Dompdf();

        $dompdf->loadHtml($view);

        $orietation = ($post['temFinalTitulo'] === true ? 'landscape' : 'portrait');

        $dompdf->setPaper('A4', $orietation);

        $dompdf->render();
        $dompdf->stream($nomeArquivo, ['Attachment' => false]);

        unset($data);
        unset($dompdf);

        exit();
    }
}
