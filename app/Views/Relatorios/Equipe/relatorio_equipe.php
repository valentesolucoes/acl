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


    <?php if(empty($usuarios)): ?>

    <h3 class="color">Não há dados para serem exibidos</h3>

    <?php else: ?>

    <div>

        <h3 class="color"><?php echo $titulo; ?></h3>
        <h4 class="color"><?php echo $periodo; ?></h4>

    </div>

    <table id="pdf">
        <thead>
            <tr>
                <th scope="col">ID usuário</th>
                <th scope="col">Nome</th>
                <th scope="col">Quantidade de ordens</th>
            </tr>
        </thead>
        <tbody>


            <?php foreach ($usuarios as $usuario): ?>

            <tr>
                <td><?php echo esc($usuario->id); ?></td>
                <td><?php echo esc($usuario->nome); ?></td>
                <td><?php echo esc($usuario->quantidade_ordens); ?></td>
            </tr>

            <?php endforeach; ?>

        </tbody>

    </table>


    <?php endif; ?>

</div>