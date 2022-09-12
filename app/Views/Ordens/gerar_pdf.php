<style>
#body-pdf {
    font-family: Arial, Helvetica, sans-serif;
}

#pdf {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

#pdf td,
#pdf th {
    border: 1px solid #ddd;
    padding: 8px;
}

#pdf tr:nth-child(even) {
    background-color: #f2f2f2;
}

#pdf tr:hover {
    background-color: #ddd;
}

#pdf th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #04AA6D;
    color: white;
}

.color {
    color: #04AA6D;
}
</style>

<div id="body-pdf">

    <div>

        <h3 class="color"><?php echo esc($ordem->nome); ?></h3>
        <p><strong class="color">Código da ordem:</strong><?php echo esc($ordem->codigo); ?></p>
        <p><strong class="color">Situação da ordem:</strong><?php echo $ordem->exibeSituacao(); ?></p>
        <p><strong class="color">Ordem aberta por:</strong><?php echo $ordem->usuario_abertura; ?></p>
        <p><strong class="color">Responsável
                técnico:</strong><?php echo ($ordem->usuario_responsavel != null ? $ordem->usuario_responsavel : 'Não definido'); ?>
        </p>


        <?php if($ordem->situacao === 'encerrada'): ?>

        <p><strong class="color">Ordem encerrada por:</strong><?php echo $ordem->usuario_encerramento; ?></p>

        <?php endif; ?>


        <p><strong class="color">Ordem criada:</strong><?php echo $ordem->criado_em->humanize(); ?></p>
        <p><strong class="color">Ordem atualizada:</strong><?php echo $ordem->atualizado_em->humanize(); ?></p>

    </div>


</div>


<?php if(empty($ordem->itens)): ?>


    <h4 class="color">Esta ordem ainda não possui nenhum item.</h4>


<?php else: ?>


    <table id="pdf">
    <thead>
        <tr>
            <th scope="col">Item</th>
            <th scope="col">Tipo</th>
            <th scope="col">Preço unitário</th>
            <th scope="col">Quantidade</th>
            <th scope="col">Subtotal</th>
        </tr>
    </thead>
    <tbody>

        <?php
                    
            $valorProdutos = 0;
            $valorServicos = 0;
    
        ?>

        <?php foreach ($ordem->itens as $item): ?>

        <?php
                            
            if ($item->tipo === 'produto') {
                $valorProdutos += $item->preco_venda * $item->item_quantidade;
            } else {
                $valorServicos += $item->preco_venda * $item->item_quantidade;
            }
        ?>

        <tr>
            <td><?php echo ellipsize($item->nome, 32, .5); ?></td>
            <td><?php echo esc(ucfirst($item->tipo)); ?></td>
            <td>R$ <?php echo esc(number_format($item->preco_venda, 2)); ?></td>
            <td><?php echo $item->item_quantidade; ?></td>

            <td>R$
                <?php echo esc(number_format($item->item_quantidade * $item->preco_venda, 2)); ?>
            </td>

        </tr>

        <?php endforeach; ?>

    </tbody>

    <tfoot>

        <tr>

            <td colspan="4" style="text-align: right;">

                <label>Valor produtos:</label>

            </td>

            <td>R$ <?php echo esc(number_format($valorProdutos, 2)); ?>
            </td>


        </tr>

        <tr>

            <td colspan="4" style="text-align: right;">

                <label>Valor serviços:</label>

            </td>

            <td>R$
                <?php echo esc(number_format($valorServicos, 2)); ?>
            </td>


        </tr>

        <tr>

            <td colspan="4" style="text-align: right;">

                <label>Valor desconto:</label>

            </td>

            <td>R$
                <?php echo esc(number_format($ordem->valor_desconto, 2)); ?>
            </td>


        </tr>

        <tr>

            <td colspan="4" style="text-align: right;">

                <label>Valor total com desconto:</label>

            </td>

            <td>R$
                <?php
                                            
                    $valorItens = $valorServicos + $valorProdutos;

                    echo esc(number_format($valorItens - $ordem->valor_desconto, 2)); ?>
            </td>


        </tr>

        <tr>

            <td colspan="4" style="text-align: right;">

                <label>Valor total da ordem:</label>

            </td>

            <td>R$
                <?php echo esc(number_format($valorServicos + $valorProdutos, 2)); ?></td>


        </tr>

    </tfoot>

</table>

    
<?php endif; ?>



