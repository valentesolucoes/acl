<div class="row">


    <?php if($item->id === null): ?>

    <div class="col-md-4 mb-5">

        <label>Este é um item do tipo:</label>

        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input" id="produto" name="tipo" value="produto" checked>
            <label class="custom-control-label" for="produto"><i class="fa fa-archive text-success" aria-hidden="true"></i>&nbsp;Novo produto</label>
        </div>

        <div class="custom-control custom-radio">
            <input type="radio" class="custom-control-input" id="servico" name="tipo" value="serviço">
            <label class="custom-control-label" for="servico"><i class="fa fa-wrench text-white" aria-hidden="true"></i>&nbsp;Novo serviço</label>
        </div>


    </div>


    <?php endif; ?>


    <div class="form-group col-md-12">
        <label class="form-control-label">Nome</label>
        <input type="text" name="nome" placeholder="Insira o nome do item" class="form-control"
            value="<?php echo esc($item->nome); ?>">
    </div>


    <div class="servico form-group col-md-4">
        <label class="form-control-label">Marca</label>
        <input type="text" name="marca" placeholder="Insira a marca do item" class="form-control"
            value="<?php echo esc($item->marca); ?>">
    </div>

    <div class="servico form-group col-md-4">
        <label class="form-control-label">Modelo</label>
        <input type="text" name="modelo" placeholder="Insira o modelo do item" class="form-control"
            value="<?php echo esc($item->modelo); ?>">
    </div>

    <div class="servico form-group col-md-4">
        <label class="form-control-label">Estoque</label>
        <input type="number" name="estoque" placeholder="Estoque" class="form-control"
            value="<?php echo esc($item->estoque); ?>">
    </div>

</div>

<div class="form-row">


    <div class="servico form-group col-md-4">
        <label class="form-control-label">Preço de custo</label>
        <input type="text" name="preco_custo" placeholder="Valor de custo" class="form-control money"
            value="<?php echo esc($item->preco_custo); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Preço de venda</label>
        <input type="text" name="preco_venda" placeholder="Valor de venda" class="form-control money"
            value="<?php echo esc($item->preco_venda); ?>">
    </div>


    <div class="form-group col-md-12">
        <label class="form-control-label">Descrição do item</label>
        <textarea name="descricao" class="form-control" rows="5"><?php echo esc($item->descricao); ?></textarea>
    </div>


</div>

<div class="servico custom-control custom-checkbox mb-2">

    <input type="hidden" name="controla_estoque" value="0">

    <input type="checkbox" name="controla_estoque" value="1" class="custom-control-input" id="controla_estoque"
        <?php if ($item->controla_estoque == true): ?> checked <?php endif; ?>>

    <label class="custom-control-label" for="controla_estoque">Controla estoque</label>
</div>

<div class="custom-control custom-checkbox">

    <input type="hidden" name="ativo" value="0">

    <input type="checkbox" name="ativo" value="1" class="custom-control-input" id="ativo"
        <?php if ($item->ativo == true): ?> checked <?php endif; ?>>

    <label class="custom-control-label" for="ativo">item ativo</label>
</div>