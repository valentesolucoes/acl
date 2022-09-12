<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdemModel extends Model
{
    protected $table                = 'ordens';
    protected $returnType           = 'App\Entities\Ordem';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'cliente_id',
        'codigo',
        'forma_pagamento',
        'situacao',
        'itens',
        'valor_produtos',
        'valor_servicos',
        'valor_desconto',
        'valor_ordem',
        'equipamento',
        'defeito',
        'observacoes',
        'parecer_tecnico',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'criado_em';
    protected $updatedField         = 'atualizado_em';
    protected $deletedField         = 'deletado_em';

    // Validation
    protected $validationRules    = [
        'cliente_id'        => 'required',
        'codigo'     => 'required',
        'equipamento'     => 'required',
    ];

    protected $validationMessages = [];


    public function recuperaOrdens()
    {
        $atributos = [
            'ordens.codigo',
            'ordens.criado_em',
            'ordens.deletado_em',
            'ordens.situacao',
            'clientes.nome',
            'clientes.cpf',
        ];


        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->orderBy('ordens.id', 'DESC')
                    ->withDeleted(true)
                    ->findAll();
    }


    public function recuperaOrdensClienteLogado(int $usuario_id)
    {
        $atributos = [
            'ordens.codigo',
            'ordens.criado_em',
            'ordens.deletado_em',
            'ordens.situacao',
            'clientes.nome',
            'clientes.cpf',
        ];


        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->join('usuarios', 'usuarios.id = clientes.usuario_id')
                    ->where('usuarios.id', $usuario_id)
                    ->orderBy('ordens.id', 'DESC')
                    ->findAll();
    }


    /**
     * Método responsável por recuperar a ordem de serviço.
     *
     * @param string|null $codigo
     * @return object|PageNotFoundException
     */
    public function buscaOrdemOu404(string $codigo = null){

        if($codigo === null){

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos ordem $codigo");
        }

        $atributos = [
            'ordens.*',
            'u_aber.id AS usuario_abertura_id', // ID do usuário que abriu a ordem
            'u_aber.nome AS usuario_abertura', // nome do usuário que abriu a ordem

            'u_resp.id AS usuario_responsavel_id', // ID do usuário que trabalhou na ordem
            'u_resp.nome AS usuario_responsavel', // Nome do usuário que trabalhou na ordem

            'u_ence.id AS usuario_encerramento_id', // ID do usuário que encerrou a ordem
            'u_ence.nome AS usuario_encerramento', // Nome do usuário que encerrou a ordem

            'clientes.usuario_id AS cliente_usuario_id', // usaremos para o acesso do cliente ao sistema
            'clientes.nome',
            'clientes.cpf', // obrigatório para gerar o boleto com a gerencianet
            'clientes.telefone', // obrigatório para gerar o boleto com a gerencianet
            'clientes.email', // obrigatório para gerar o boleto com a gerencianet
        ];


        $ordem = $this->select($atributos)
                      ->join('ordens_responsaveis', 'ordens_responsaveis.ordem_id = ordens.id')

                      ->join('clientes', 'clientes.id = ordens.cliente_id')

                      ->join('usuarios AS u_cliente', 'u_cliente.id = clientes.usuario_id')

                      ->join('usuarios AS u_aber', 'u_aber.id = ordens_responsaveis.usuario_abertura_id')

                      ->join('usuarios AS u_resp', 'u_resp.id = ordens_responsaveis.usuario_responsavel_id', 'LEFT') // LEFT, pois pode ser que a ordem ainda não possua um técnico responsável
                      
                      ->join('usuarios AS u_ence', 'u_ence.id = ordens_responsaveis.usuario_encerramento_id', 'LEFT') // LEFT, pois pode ser que a ordem ainda não tenha sido encerrada

                      ->where('ordens.codigo', $codigo)
                      ->withDeleted(true)
                      ->first();

        if($ordem === null){

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos ordem $codigo");
        }


        return $ordem;
        

    }


    /**
     * Método que gera o código interno da ordem de serviço
     *
     * @return string
     */
    public function geraCodigoOrdem() : string
    {
        do {
            $codigo = strtoupper(random_string('alnum', 20));

            $this->select('codigo')->where('codigo', $codigo);
        } while ($this->countAllResults() > 1);


        return $codigo;
    }


    public function recuperaOrdensPelaSituacao(string $situacao, string $dataInicial, string $dataFinal){

        switch ($situacao) {

            case 'aberta':
                
                $campoDate = 'criado_em';

                break;

            case 'encerrada':
            case 'aguardando':
            case 'cancelada':
            case 'nao_pago':

                $campoDate = 'atualizado_em';

                break;
        }


        $dataInicial = str_replace('T', ' ', $dataInicial);
		$dataFinal = str_replace('T', ' ', $dataFinal);

        $atributos = [
            'ordens.codigo',
            'ordens.situacao',
            'ordens.valor_ordem',
            'ordens.criado_em',
            'ordens.atualizado_em',
            'ordens.deletado_em',
            'clientes.nome',
            'clientes.cpf',
        ];


        $where = 'ordens.' .$campoDate. ' BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';        

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where('situacao', $situacao)
                    ->where($where)
                    ->orderBy('situacao', 'ASC')
                    //->getCompiledSelect();
                    ->findAll();

    }

    public function recuperaOrdensExcluidas(string $dataInicial, string $dataFinal){

    
        $dataInicial = str_replace('T', ' ', $dataInicial);
		$dataFinal = str_replace('T', ' ', $dataFinal);

        $atributos = [
            'ordens.codigo',
            'ordens.situacao',
            'ordens.valor_ordem',
            'ordens.criado_em',
            'ordens.atualizado_em',
            'ordens.deletado_em',
            'clientes.nome',
            'clientes.cpf',
        ];


        $where = 'ordens.deletado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';        

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where($where)
                    ->onlyDeleted()
                    ->orderBy('situacao', 'ASC')
                    //->getCompiledSelect();
                    ->findAll();

    }


    public function recuperaOrdensComBoleto($dataInicial, string $dataFinal){
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        $dataInicial = str_replace('T', ' ', $dataInicial);
		$dataFinal = str_replace('T', ' ', $dataFinal);

        $atributos = [
            'ordens.codigo',
            'ordens.situacao',
            'ordens.valor_ordem',
            'transacoes.charge_id',
            'transacoes.expire_at',
        ];


        $where = 'ordens.atualizado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
                    ->join('transacoes', 'transacoes.ordem_id = ordens.id')
                    ->where($where)
                    ->withDeleted(true)
                    ->orderBy('situacao', 'ASC')
                    ->groupBy('ordens.codigo')
                    //->getCompiledSelect();
                    ->findAll();


    }


    public function recuperaClientesMaisAssiduos(string $anoEscolhido){
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        $atributos = [
            'clientes.id',
            'clientes.nome',
            'COUNT(*) AS ordens',
            'SUM(ordens.valor_ordem) AS valor_gerado',
            'YEAR(ordens.criado_em) AS ano',
        ];


        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where('YEAR(ordens.criado_em)', $anoEscolhido)
                    ->where('ordens.situacao', 'encerrada')
                    ->where('ordens.valor_ordem !=', null)
                    ->withDeleted(true)
                    ->groupBy('clientes.nome')
                    ->orderBy('ordens', 'DESC')
                    ->findAll();


    }



    public function recuperaOrdensPorMesGrafico(string $anoEscolhido){
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 

        $atributos = [
            'COUNT(id) AS total_ordens',
            'YEAR(criado_em) AS ano',
            'MONTH(criado_em) AS mes_numerico',
            'MONTHNAME(criado_em) AS mes_nome',
        ];

        return $this->select($atributos)
                    ->where('YEAR(criado_em)', $anoEscolhido)
                    ->groupBy('mes_nome')
                    ->groupBy('mes_numerico', 'ASC')
                    ->findAll();

    }
}
