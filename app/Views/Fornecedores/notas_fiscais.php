<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-5">

        <div class="block">

            <div class="block-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response">


                </div>


                <?php echo form_open_multipart('/', ['id' => 'form'], ['id' => "$fornecedor->id"]) ?>


                <div class="form-group">
                    <label class="form-control-label">Valor da nota fiscal</label>
                    <input type="text" name="valor_nota" placeholder="Insira o valor" class="form-control money">
                </div>

                <div class="form-group">
                    <label class="form-control-label">Data da emissão da nota</label>
                    <input type="date" name="data_emissao" placeholder="Data emissão" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-control-label">Arquivo em PDF da nota fiscal</label>
                    <input type="file" name="nota_fiscal" class="form-control">
                </div>


                <div class="form-group">
                    <label class="form-control-label">Breve descrição dos itens da nota fiscal</label>
                    <textarea name="descricao_itens" class="form-control" placeholder="Insira a descrição dos itens..."></textarea>
                </div>


                <div class="form-group mt-5 mb-2">

                    <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
                    <a href="<?php echo site_url("fornecedores/exibir/$fornecedor->id") ?>"
                        class="btn btn-secondary btn-sm ml-2">Voltar</a>

                </div>


                <?php echo form_close(); ?>


            </div>



        </div> <!-- ./ block -->

    </div>


    <div class="col-lg-7">


        <div class="user-block block">


            <?php if (empty($fornecedor->notas_fiscais)): ?>

            <p class="contributions text-warning mt-0">Esse fornecedor ainda não possui notas fiscais!</p>

            <?php else: ?>

            <div class="table-responsive">

                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Data de emissão</th>
                            <th>Valor da nota</th>
                            <th>Descrição dos itens</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($fornecedor->notas_fiscais as $nota): ?>
                        <tr>
                            <td><?php echo date('d/m/Y',strtotime($nota->data_emissao)); ?></td>
                            <td>R$&nbsp;<?php echo number_format($nota->valor_nota, 2); ?></td>
                            <td><?php echo ellipsize($nota->descricao_itens, 20, .5); ?></td>
                            <td class="text-center">

                                <?php 
                                    $atributos = [
                                        'onSubmit' => "return confirm('Tem certeza da exclusão da nota fiscal?');",
                                    ]; 
                                ?>

                                <?php echo form_open("fornecedores/removenota/$nota->nota_fiscal", $atributos) ?>

                                    <a target="_blank" href="<?php echo site_url("fornecedores/exibirnota/$nota->nota_fiscal") ?>" class="btn btn-sm btn-outline-primary mr-2"><i class="fa fa-eye"></i></a>                                               

                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>

                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

                <div class="mt-3 ml-1">
                    <?php echo $fornecedor->pager->links(); ?>
                </div>

            </div> <!-- ./ block -->

            <?php endif; ?>

        </div>


    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>


<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>


<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>


<script>
$(document).ready(function() {

    //$("#form").LoadingOverlay("show");

    


    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('fornecedores/cadastrarnotafiscal'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {

                $("#response").html('');
                $("#btn-salvar").val('Por favor aguarde...');

            },
            success: function(response) {

                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");

                $('[name=csrf_ordem]').val(response.token);


                if (!response.erro) {


                        // Tudo certo com a atualização do usuário
                        // Podemos agora redirecioná-lo tranquilamente

                        window.location.href =
                            "<?php echo site_url("fornecedores/notas/$fornecedor->id"); ?>";

                }

                if (response.erro) {

                    // Exitem erros de validação


                    $("#response").html('<div class="alert alert-danger">' + response.erro +
                        '</div>');


                    if (response.erros_model) {


                        $.each(response.erros_model, function(key, value) {

                            $("#response").append(
                                '<ul class="list-unstyled"><li class="text-danger">' +
                                value + '</li></ul>');

                        });

                    }

                }

            },
            error: function() {

                alert(
                    'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.'
                );
                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");

            }



        });


    });


    $("#form").submit(function() {

        $(this).find(":submit").attr('disabled', 'disabled');

    });


});
</script>


<?php echo $this->endSection() ?>