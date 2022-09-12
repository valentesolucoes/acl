<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\OrdemTrait;
use App\Transacao\Gerencianet\Operacoes;

class Transacoes extends BaseController
{
    private $transacaoModel;
    private $ordemModel;
    private $eventoModel;


    public function __construct()
    {
        $this->transacaoModel = new \App\Models\TransacaoModel();
        $this->ordemModel = new \App\Models\OrdemModel();
        $this->eventoModel = new \App\Models\EventoModel();
    }

    public function editar(string $codigo = null)
    {
        if (! $this->usuarioLogado()->temPermissaoPara('alterar-vencimento-transacao')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao === 'encerrada') {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Esta ordem já encontra-se encerrada");
        }

        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao === null) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Não encontramos uma transacao associada à ordem de serviço $codigo");
        }

        

        if ($transacao->status === 'canceled') {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Apenas transações com o status [ Aguardando ] ou [ Não paga ] podem ser atualizadas.");
        }


        $ordem->transacao = $transacao;


        $data = [
            'titulo' => "Definir nova data de vencimento da ordem $ordem->codigo",
            'ordem' => $ordem
        ];

        return view('Ordens/Transacoes/editar', $data);
    }


    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }


        // Envio o hash do token do form
        $retorno['token'] = csrf_hash();


        $validacao = service('validation');


        $post = $this->request->getPost();


        $regras = [
            'data_vencimento' => 'required',
        ];

        $mensagens = [   // Errors
            'data_vencimento' => [
                'required' => 'Informe a nova data de vencimento',
            ],
        ];

        $validacao->setRules($regras, $mensagens);


        if ($validacao->withRequest($this->request)->run() === false) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = $validacao->getErrors();


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        if ($post['data_vencimento'] < date('Y-m-d')) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['data_vencimento' => 'A data de vencimento não pode ser menor que a data atual'];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);


        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao === null) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['transacao' => "Não encontramos uma transacao associada à ordem de serviço $ordem->codigo"];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        if ($transacao->status === 'paid') {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['transacao' => "Só é possível editar a data de vencimento de uma ordem que ainda não foi paga. No momento ela está " . $ordem->exibeSituacao()];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        if ($post['data_vencimento'] == $transacao->expire_at) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['data_vencimento' => "A nova data de vencimento não pode ser igual anterior"];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }

        // A gerencianet não permite a antecipação da data de vencimento do boleto
        if ($post['data_vencimento'] < substr($transacao->expire_at, 0, 10)) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['data_vencimento' => "Não é possível antecipar a data de vencimento do boleto"];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        $transacao->expire_at = $post['data_vencimento'];


        $ordem->transacao = $transacao;

        $objetoOperacao = new Operacoes($ordem);

        $objetoOperacao->alteraVencimentoTransacao();


        if (isset($ordem->erro_transacao)) {
            $retorno['erro'] = 'Por favor verifique os abaixo e tente novamente';
            $retorno['erros_model'] = ['erro_transacao' => $ordem->erro_transacao];


            // Retorno para o ajax request
            return $this->response->setJSON($retorno);
        }


        session()->setFlashdata('sucesso', 'Nova data de vencimento definida com sucesso!');
        return $this->response->setJSON($retorno);
    }


    public function cancelar(string $codigo = null)
    {
        if (! $this->usuarioLogado()->temPermissaoPara('cancelar-transacao')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao === 'encerrada') {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Esta ordem já encontra-se encerrada");
        }

        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao === null) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Não encontramos uma transacao associada à ordem de serviço $codigo");
        }

        
        $statusAceitos = [
            'new',
            'waiting',
            'unpaid',
        ];

        if (! in_array($transacao->status, $statusAceitos)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Apenas transações com o status [ Aguardando ] ou [ Não paga ] podem ser cancelados.");
        }


        $ordem->transacao = $transacao;


        $objetoOperacao = new Operacoes($ordem);

        $objetoOperacao->cancelarTransacao();


        if (isset($ordem->erro_transacao)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', ['erro_transacao' => $ordem->erro_transacao]);
        }


        return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('sucesso', "Boleto cancelado com sucesso!");
    }


    public function reenviar(string $codigo = null)
    {
        if (! $this->usuarioLogado()->temPermissaoPara('reenviar-boleto-transacao')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao === 'encerrada') {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Esta ordem já encontra-se encerrada");
        }

        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao === null) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Não encontramos uma transacao associada à ordem de serviço $codigo");
        }

        
        $statusAceitos = [
            'new',
            'waiting',
            'unpaid',
        ];

        if (! in_array($transacao->status, $statusAceitos)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Apenas transações com o status [ Aguardando ] ou [ Não paga ] podem ser cancelados.");
        }


        $ordem->transacao = $transacao;


        $objetoOperacao = new Operacoes($ordem);

        $objetoOperacao->reenviarBoleto();


        if (isset($ordem->erro_transacao)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', ['erro_transacao' => $ordem->erro_transacao]);
        }


        return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('sucesso', "Boleto reenviado com sucesso!");
    }


    public function consultar(string $codigo = null)
    {

        if (! $this->usuarioLogado()->temPermissaoPara('consultar-transacao')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao === null) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Não encontramos uma transacao associada à ordem de serviço $codigo");
        }


        $ordem->transacao = $transacao;


        $objetoOperacao = new Operacoes($ordem);

        $objetoOperacao->contultarTransacao();


        if (isset($ordem->erro_transacao)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', ['erro_transacao' => $ordem->erro_transacao]);
        }


        return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('sucesso', "Transação consultada com sucesso!<br><br><b>Histórico</b>".$ordem->formaTextoHistorico());
    }


    public function pagar(string $codigo = null)
    {
        if (! $this->usuarioLogado()->temPermissaoPara('pagar-transacao')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome. ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao === 'encerrada') {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Esta ordem já encontra-se encerrada");
        }

        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao === null) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Não encontramos uma transacao associada à ordem de serviço $codigo");
        }

        
        $statusAceitos = [
            'new',
            'waiting',
            'unpaid',
        ];

        if (! in_array($transacao->status, $statusAceitos)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', "Apenas transações com o status [ Aguardando ] ou [ Não paga ] podem ser cancelados.");
        }


        $ordem->transacao = $transacao;


        $objetoOperacao = new Operacoes($ordem);

        $objetoOperacao->marcarComoPaga();


        if (isset($ordem->erro_transacao)) {
            return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('atencao', ['erro_transacao' => $ordem->erro_transacao]);
        }


        return redirect()->back()
                             ->with('transacao', '') // usamos para ativar a tab transacoes na view de detalhes
                             ->with('sucesso', "Boleto marcado como pago com sucesso!");
    }

    public function notificacoes()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(400, 'Método inválido');
        }


        $tokenNotificacao = trim($this->request->getPost('notification'));

        if ($tokenNotificacao == null || empty($tokenNotificacao)) {
            return $this->response->setStatusCode(400, 'Token de notificação inválido');
        }

        
        $objetoOperacao = new Operacoes();
        $objetoOperacao->consultaNotificacao($tokenNotificacao);

        return $this->response->setStatusCode(200, 'Notificação recebida e tratada!');
    }
}
