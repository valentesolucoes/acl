<div class="row">

    <div class="form-group col-md-12">
        <label class="form-control-label">Nome completo</label>
        <input type="text" name="nome" placeholder="Insira o nome completo" class="form-control" value="<?php echo esc($cliente->nome); ?>">
    </div>


    <div class="form-group col-md-4">
        <label class="form-control-label">CPF</label>
        <input type="text" name="cpf" placeholder="Insira o CPF" class="form-control cpf" value="<?php echo esc($cliente->cpf); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Telefone</label>
        <input type="text" name="telefone" placeholder="Insira o telefone" class="form-control sp_celphones" value="<?php echo esc($cliente->telefone); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">E-mail (para acesso ao sistema)</label>
        <input type="text" name="email" placeholder="Insira o email" class="form-control" value="<?php echo esc($cliente->email); ?>">
        <div id="email"></div>
    </div>


    <div class="form-group col-md-4">
        <label class="form-control-label">CEP</label>
        <input type="text" name="cep" placeholder="Insira o CEP" class="form-control cep" value="<?php echo esc($cliente->cep); ?>">
        <div id="cep"></div>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Endereço</label>
        <input type="text" name="endereco" placeholder="Insira o endereço" class="form-control" value="<?php echo esc($cliente->endereco); ?>" readonly>
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Nº</label>
        <input type="text" name="numero" placeholder="Insira o Nº" class="form-control" value="<?php echo esc($cliente->numero); ?>">
    </div>

    <div class="form-group col-md-4">
        <label class="form-control-label">Bairro</label>
        <input type="text" name="bairro" placeholder="Insira o Bairro" class="form-control" value="<?php echo esc($cliente->bairro); ?>" readonly>
    </div>

    <div class="form-group col-md-6">
        <label class="form-control-label">Cidade</label>
        <input type="text" name="cidade" placeholder="Insira a Cidade" class="form-control" value="<?php echo esc($cliente->cidade); ?>" readonly>
    </div>

    <div class="form-group col-md-2">
        <label class="form-control-label">Estado</label>
        <input type="text" name="estado" placeholder="U.F" class="form-control" value="<?php echo esc($cliente->estado); ?>" readonly>
    </div>

</div>
