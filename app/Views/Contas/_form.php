<?php if ($conta->id === null): ?>

<div class="form-group">
    <label class="form-control-label">Escolha o Fornecedor</label>

    <select name="fornecedor_id" class="selectize">

        <option value="">Digite o nome do fornecedor ou CNPJ</option>

    </select>
</div>

<?php else: ?>

<div class="form-group">
    <label class="form-control-label">Fornecedor</label>
    <a tabindex="0" style="text-decoration: none;" role="button" data-toggle="popover" data-trigger="focus"
        title="Importante" data-content="Não é permitido editar o Fornecedor da conta">
        &nbsp;&nbsp;<i class="fa fa-question-circle fa-lg text-info"></i>
    </a>
    <input type="text" class="form-control" disabled readonly value="<?php echo esc($conta->razao); ?>">
</div>

<?php endif; ?>



<div class="form-group">
    <label class="form-control-label">Valor da conta</label>
    <input type="text" name="valor_conta" placeholder="Insira o valor" class="form-control money"
        value="<?php echo esc($conta->valor_conta); ?>">
</div>

<div class="form-group">
    <label class="form-control-label">Data de vencimento</label>
    <input type="date" name="data_vencimento" placeholder="Insira a data" class="form-control"
        value="<?php echo esc($conta->data_vencimento); ?>">
</div>

<div class="form-group">
    <label class="form-control-label">Descrição da conta</label>
    <textarea class="form-control" name="descricao_conta"><?php echo esc($conta->descricao_conta); ?></textarea>
</div>




<div class="custom-control custom-radio mb-2">

    <input type="radio" name="situacao" value="0" class="custom-control-input" id="aberta"
        <?php if ($conta->situacao == false): ?> checked <?php endif; ?>>

    <label class="custom-control-label" for="aberta">Esta conta está em aberto</label>

</div>


<div class="custom-control custom-radio mb-2">

    <input type="radio" name="situacao" value="1" class="custom-control-input" id="paga"
        <?php if ($conta->situacao == true): ?> checked <?php endif; ?>>

    <label class="custom-control-label" for="paga">Esta conta está paga</label>

</div>