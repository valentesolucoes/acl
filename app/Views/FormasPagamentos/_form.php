<div class="form-group">
    <label class="form-control-label">Nome</label>
    <input type="text" name="nome" placeholder="Insira o nome da forma de pagamento" class="form-control" value="<?php echo esc($forma->nome); ?>">
</div>


<div class="form-group">
    <label class="form-control-label">Descrição</label>
    <textarea name="descricao" placeholder="Insira a descrição da forma de pagamento" class="form-control"><?php echo esc($forma->descricao); ?></textarea>
</div>


<div class="custom-control custom-checkbox">

  <input type="hidden" name="ativo" value="0">

  <input type="checkbox" name="ativo" value="1" class="custom-control-input" id="ativo" <?php if ($forma->ativo == true): ?> checked <?php endif; ?> >

  <label class="custom-control-label" for="ativo">Forma de pagamento ativa</label>

</div>