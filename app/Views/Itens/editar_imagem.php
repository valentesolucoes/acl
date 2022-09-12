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


                <?php if(count($item->imagens) >= 10): ?>

                <p class="contributions text-danger mt-0">Esse produto já possui as
                    <?php echo count($item->imagens); ?> imagens permitidas.<br>
                    Para inserir novas imagens, você precisa remover algumas das existentes.
                </p>

                <?php else: ?>

                <!-- Exibirá os retornos do backend -->
                <div id="response">


                </div>


                <?php echo form_open_multipart('/', ['id' => 'form'], ['id' => "$item->id"]) ?>


                <div class="form-group">
                    <label class="form-control-label">Escolha uma ou mais imagens</label>
                    <input type="file" name="imagens[]" class="form-control" multiple>
                </div>


                <div class="form-group mt-5 mb-2">

                    <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
                    <a href="<?php echo site_url("itens/exibir/$item->id") ?>"
                        class="btn btn-secondary btn-sm ml-2">Voltar</a>

                </div>


                <?php echo form_close(); ?>


                <?php endif; ?>





            </div>



        </div> <!-- ./ block -->

    </div>


    <div class="col-lg-7">


        <div class="user-block block">


            <?php if (empty($item->imagens)): ?>

            <p class="contributions text-warning mt-0">Esse item ainda não possui nenhuma imagem!</p>

            <?php else: ?>


            <ul class="list-inline">

                <?php foreach($item->imagens as $imagem): ?>

                <li class="list-inline-item">

                    <div class="card" style="width: 10rem;">

                        <img class="card-img-top" src="<?php echo site_url("itens/imagem/$imagem->imagem"); ?>"
                            alt="<?php echo esc($item->nome); ?>">

                    </div>

                    <div class="card-body text-center">

                        <?php 
                                    $atributos = [
                                        'onSubmit' => "return confirm('Tem certeza da exclusão da imagem?');",
                                    ]; 
                                ?>

                        <?php echo form_open("itens/removeimagem/$imagem->imagem", $atributos) ?>

                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>

                        <?php echo form_close(); ?>


                    </div>

                </li>


                <?php endforeach; ?>

            </ul>


            <?php endif; ?>

        </div>


    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>



<script src="<?php echo site_url('recursos/vendor/mask/jquery.mask.min.js') ?>"></script>
<script src="<?php echo site_url('recursos/vendor/mask/app.js') ?>"></script>


<script>
$(document).ready(function() {


    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('itens/upload'); ?>',
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
                        "<?php echo site_url("itens/editarimagem/$item->id"); ?>";

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