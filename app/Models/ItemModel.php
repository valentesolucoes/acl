<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table                = 'itens';
    protected $returnType           = 'App\Entities\Item';
    protected $useSoftDeletes       = true;
    protected $allowedFields        = [
        'codigo_interno',
        'nome',
        'marca',
        'modelo',
        'preco_custo',
        'preco_venda',
        'estoque',
        'controla_estoque',
        'tipo',
        'ativo',
        'descricao',
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'criado_em';
    protected $updatedField         = 'atualizado_em';
    protected $deletedField         = 'deletado_em';


    // Validations
    // Validation
    protected $validationRules    = [
        'nome'        => 'required|max_length[120]|is_unique[itens.nome,id,{id}]', // Não pode ter espaços
        'preco_venda'     => 'required',
        'descricao' => 'required'
    ];

    protected $validationMessages = [];


    // Callbacks
    protected $beforeInsert         = ['removeVirgulaValores'];
    protected $beforeUpdate         = ['removeVirgulaValores'];


    protected function removeVirgulaValores(array $data)
    {
        if (isset($data['data']['preco_custo'])) {
            $data['data']['preco_custo'] = str_replace(",", "", $data['data']['preco_custo']);
        }

        if (isset($data['data']['preco_venda'])) {
            $data['data']['preco_venda'] = str_replace(",", "", $data['data']['preco_venda']);
        }

        return $data;
    }


    /**
     * Método que gera o código interno do item na hora de cadastrá-lo
     *
     * @return string
     */
    public function geraCodigoInternoItem() : string
    {
        do {
            $codigoInterno = random_string('numeric', 15);

            $this->where('codigo_interno', $codigoInterno);
        } while ($this->countAllResults() > 1);


        return $codigoInterno;
    }


    /**
     * Método responsável por recuperar os itens de acordo com o termo digitado no autocomplete da view itens de ordem de serviço
     *
     * @param string|null $term
     * @return array
     */
    public function pesquisaItens(string $term = null) : array
    {
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        if ($term === null) {
            return [];
        }

        $atributos = [
            'itens.*',
            'itens_imagens.imagem',
        ];


        $itens = $this->select($atributos)
                      ->like('itens.nome', $term)
                      ->orLike('itens.codigo_interno', $term)
                      ->join('itens_imagens', 'itens_imagens.item_id = itens.id', 'LEFT') // LEFT para os itens sem imagem serem recuperados
                      ->where('itens.ativo', true)
                      ->where('deletado_em', null)
                      ->groupBy('itens.nome') // para não repetir os registros
                      ->findAll();
        
        
        if ($itens === null) {

            // Nenhum item combina com o termo digitado
            return [];
        }


        // Verifico se existe nas opções encontradas algum item do tipo produto,
        // que esteja com estoque abaixo de 1 (0 ou negativo)
        foreach ($itens as $key => $item) {
            if ($item->tipo === 'produto' && $item->estoque < 1) {
                unset($itens[$key]);
            }
        }

        // Retorno o array de itens
        return $itens;
    }


    /**
     * Método responsável por realizar a baixa no estoque de itens do tipo produto e que estejam o controle de estoque ativado.
     *
     * @param array $produtos
     * @return void
     */
    public function realizaBaixaNoEstoqueDeProdutos(array $produtos)
    {
        $arrayIds = array_column($produtos, 'id');


        // Recupero no banco os produtos de acordo com os ID's contidos em $arrayIds
        $produtosEstoque = $this->select('id, estoque')->whereIn('id', $arrayIds)->asArray()->findAll();

        // Receberá os produtos que serão atualizados o estoque
        $arrayEstoque = [];

        foreach ($produtos as $produto) {
            foreach ($produtosEstoque as $pEstoque) {
                if ($produto['id'] == $pEstoque['id']) {
                    $novaQuantidadeEstoque = $pEstoque['estoque'] - $produto['quantidade'];

                    array_push($arrayEstoque, [
                        'id' => $pEstoque['id'],
                        'estoque' => $novaQuantidadeEstoque
                    ]);
                }
            }
        }

        return $this->updateBatch($arrayEstoque, 'id');
    }
}
