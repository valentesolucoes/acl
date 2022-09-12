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


    <?php if(empty($itens)): ?>

    <h3 class="color">Não há informações de itens mais vendidos na base de dados</h3>

    <?php else: ?>

    <div>

        <h4 class="color"><?php echo $titulo; ?></h4>
        <h5 class="color"><?php echo $periodo; ?></h5>

    </div>

    <table id="pdf">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Código interno</th>
                <th scope="col">Tipo</th>
                <th scope="col">Vendidos</th>
            </tr>
        </thead>
        <tbody>


            <?php foreach ($itens as $item): ?>

            <tr>
                <td><?php echo word_limiter($item->nome, 10); ?></td>
                <td><?php echo esc($item->codigo_interno); ?></td>
                <td><?php echo esc(ucfirst($item->tipo)); ?></td>
                <td><?php echo esc($item->quantidade); ?></td>

            </tr>

            <?php endforeach; ?>

        </tbody>

    </table>


    <?php endif; ?>






</div>