
<h3>Olá, <?php echo esc($ordem->nome); ?></h3>

<p>Sua ordem de serviço foi <strong>encerrada<strong></p>


<p><strong>Informações da ordem de serviço:<strong></p>

<p>
    <strong>Equipamento: </strong> <?php echo esc($ordem->equipamento); ?>
</p>

<p>
    <strong>Defeito: </strong> <?php echo esc($ordem->defeito != null ? $ordem->defeito : 'Não informado'); ?>
</p>

<p>
    <strong>Observações: </strong> <?php echo esc($ordem->observacoes != null ? $ordem->observacoes : 'Não informado'); ?>
</p>

<p>
    <strong>Parecer técnico: </strong> <?php echo esc($ordem->parecer_tecnico); ?>
</p>

<p>
    <strong>Data de abertura: </strong> <?php echo date('d/m/Y H:i', strtotime($ordem->criado_em)); ?>
</p>


<?php if ($ordem->itens === null): ?>

    <p>Nenhum item foi adicionado à ordem até o momento</p>

<?php else: ?>


    <?php

        $valorProdutos = 0;
        $valorServicos = 0;

        foreach ($ordem->itens as $item) {
            if ($item->tipo === 'produto') {
                $valorProdutos += $item->preco_venda * $item->item_quantidade;
            } else {
                $valorServicos += $item->preco_venda * $item->item_quantidade;
            }
        }
    ?>

    
<p>
    <strong>Valores finais: </strong>
</p>
    
<p>
    <strong>Valor de produtos: R$&nbsp;<?php echo number_format($valorProdutos, 2); ?></strong>
</p>
<p>
    <strong>Valor de serviços: R$&nbsp;<?php echo number_format($valorServicos, 2); ?></strong>
</p>
<p>
    <strong>Valor de desconto: R$&nbsp;<?php echo number_format($ordem->valor_desconto, 2); ?></strong>
</p>
<p>
    <strong>Valor total sem desconto: R$&nbsp;<?php echo number_format($valorServicos + $valorProdutos, 2); ?></strong>
</p>
<p>
    <strong>Valor total com desconto: R$&nbsp;<?php echo number_format(($valorServicos + $valorProdutos) - $ordem->valor_desconto, 2); ?></strong>
</p>
<p>
    <strong>Forma de pagamento:&nbsp;<?php echo esc($ordem->forma_pagamento); ?></strong>
</p>

<?php endif; ?>


<hr>

<p>
    Não deixe de consultar <a target="_blank" href="<?php echo site_url("ordens/minhas") ?>">as suas ordens de serviços</a>
</p>

<small>Não é necessário responder esse e-mail</small>