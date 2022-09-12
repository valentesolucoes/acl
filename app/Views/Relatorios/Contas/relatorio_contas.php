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


    <?php if(empty($contas)): ?>

    <h3 class="color">Não há dados para serem exibidos no momento</h3>

    <?php else: ?>

    <div>

        <h3 class="color"><?php echo $titulo; ?></h3>

        <?php if(isset($periodo)): ?>
            <h4 class="color"><?php echo $periodo; ?></h4>
        <?php endif ?>

    </div>

    <table id="pdf">
        <thead>
            <tr>
                <th scope="col">Fornecedor</th>
                <th scope="col">Situação da conta</th>
                <th scope="col">Valor da conta</th>
            </tr>
        </thead>
        <tbody>


            <?php foreach ($contas as $conta): ?>

            <tr>
                <td><?php echo $conta->razao . ' - CNPJ ' .$conta->cnpj; ?></td>
                <td><?php echo $conta->exibeSituacao(); ?></td>
                <td>R$ <?php echo number_format($conta->valor_conta, 2) ?></td>

            </tr>

            <?php endforeach; ?>

        </tbody>

    </table>


    <?php endif; ?>

</div>