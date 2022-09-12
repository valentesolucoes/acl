<?php


namespace App\Traits;

trait OrdemTrait
{

    /**
     * Método que prepara a exibição dos possíveis itens da ordem de serviço
     *
     * @param object $ordem
     * @return object
     */
    public function preparaItensDaOrdem(object $ordem) : object {

        $ordemItemModel = new \App\Models\OrdemItemModel();


        if($ordem->situacao === 'aberta'){

            $ordemItens = $ordemItemModel->recuperaItensDaOrdem($ordem->id);


            $ordem->itens = (!empty($ordemItens) ? $ordemItens : null);

            return $ordem;
        }

        // Nesse ponto a ordem já não está mais em aberto.
        // Pode ter sido encerrada com cartão, boleto, etc.
        if($ordem->itens !== null){

            $ordem->itens = unserialize($ordem->itens);

        }

        //Retorno a ordem
        return $ordem;

    }


    public function preparaOrdemParaEncerrar(object $ordem, object $formaPagamento) : object {

        // Defino a situação da ordem
        $ordem->situacao = ($formaPagamento->id == 1 ? 'aguardando' : 'encerrada');


        if($ordem->itens === null){

            // Ordem não gerou valor

            $ordem->forma_pagamento = 'Cortesia';

            $ordem->valor_produtos = null;
            $ordem->valor_servicos = null;
            $ordem->valor_desconto = null;
            $ordem->valor_ordem = null;

            return $ordem;

        }


        // Nesse ponto a ordem tem pelo menos um item.... portanto, gerou valor.... damos sequência

        $ordem->forma_pagamento = esc($formaPagamento->nome);


        // Declaro duas variáveis para receber os seus recpectivos valores
        $valorProdutos = null;
        $valorServicos = null;


        // Receberá o push dos itens do tipo produto para ser realizada abaixa no estoque
        $produtos = [];


        foreach($ordem->itens as $item){


            if($item->tipo === 'produto'){

                $valorProdutos += $item->preco_venda * $item->item_quantidade;

                if($item->controla_estoque == true){

                    array_push($produtos, [
                        'id' => $item->id,
                        'quantidade' => (int) $item->item_quantidade,
                    ]);

                }

            }else{


                // Aqui é um serviço
                $valorServicos += $item->preco_venda * $item->item_quantidade;


            }

        }


        if( ! empty($produtos)){

            $ordem->produtos = $produtos;

        }

        
        // Defino o valor dos produtos e serviços da ordem
        $ordem->valor_produtos = str_replace(',', '', number_format($valorProdutos, 2));
        $ordem->valor_servicos = str_replace(',', '', number_format($valorServicos, 2));


        // Forma boleto
        if($formaPagamento->id == 1){

            $valor = $valorProdutos + $valorServicos;

            $porcentagem = (int) getenv('gerenciaNetDesconto') / 100;

            // Sobrescrevemos o valor de desconto que possa existir
            $ordem->valor_desconto = $valor * ($porcentagem / 100);

        }

        // Armazenamos o valor final da ordem
        $valorFinalOrdem = number_format(($valorProdutos + $valorServicos) - $ordem->valor_desconto, 2);

        // Defino o valor final da ordem
        $ordem->valor_ordem = str_replace(',', '', $valorFinalOrdem);

        // Serializamos os itens da ordem
        $ordem->itens = serialize($ordem->itens);


        // Retorno o objeto $ordem totalmente pronto para encerrar
        return $ordem;

    }


    /**
     * Método responsável por realizar a baixa no estoque dos produtos, quando necessário.
     *
     * @param object $ordem
     * @return void
     */
    public function gerenciaEstoqueProduto(object $ordem){


        // Receberá o push dos itens do tipo produto para ser realizada abaixa no estoque
        $produtos = [];

        $ordem->itens = unserialize($ordem->itens);


        foreach($ordem->itens as $item){


            if($item->tipo === 'produto'){

            
                if($item->controla_estoque == true){

                    array_push($produtos, [
                        'id' => $item->id,
                        'quantidade' => (int) $item->item_quantidade,
                    ]);

                }

            }

        }


        if( ! empty($produtos)){

           $itemModel = new \App\Models\ItemModel();

           $itemModel->realizaBaixaNoEstoqueDeProdutos($produtos);

        }


    }

}