<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-4">

        <div class="block">

            <div class="user-block">

                <div class="block-body">

                    <!-- Exibirá os retornos do backend -->
                    <div id="response">


                    </div>


                    <?php echo form_open('/', ['id' => 'form'], ['codigo' => "$ordem->codigo"]) ?>


                    <div class="contributions">
                        Data de vecimento atual: <?php echo date('d/m/Y', strtotime($ordem->transacao->expire_at)) ?>
                    </div>

                    <div class="form-row mt-5">


                        <div class="form-group col-md-12">

                            <label class="form-form-control-label">Nova data de vencimento</label>
                            <input type="date" name="data_vencimento" class="form-control">

                        </div>


                    </div>


                    <div class="form-group mt-5 mb-2">


                        <input id="btn-salvar" type="submit" value="Processar alteração"
                            class="btn btn-danger btn-sm mr-2">
                        <a href="<?php echo site_url("ordens/detalhes/$ordem->codigo") ?>"
                            class="btn btn-secondary btn-sm ml-2">Voltar</a>

                    </div>


                    <?php echo form_close(); ?>


                </div>



            </div> <!-- ./ block -->

        </div>

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url('recursos/vendor/loadingoverlay/loadingoverlay.min.js') ?>"></script>


<script>
$(document).ready(function() {

    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('transacoes/atualizar'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {

                $(".block").LoadingOverlay("show", {

                    image: "",
                    text: "Processando...",

                });

                $("#response").html('');
                $("#btn-salvar").val('Por favor aguarde...');

            },
            success: function(response) {

                $(".block").LoadingOverlay("hide", true);

                $("#btn-salvar").val('Processar alteração');
                $("#btn-salvar").removeAttr("disabled");

                $('[name=csrf_ordem]').val(response.token);


                if (!response.erro) {


                    if (response.info) {

                        $("#response").html('<div class="alert alert-info">' + response
                            .info + '</div>');

                    } else {

               
                        // Podemos agora redirecioná-lo tranquilamente

                        window.location.href = "<?php echo site_url("ordens/detalhes/$ordem->codigo"); ?>";

                    }

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

                $(".block").LoadingOverlay("hide", true);

                alert(
                    'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.'
                    );
                $("#btn-salvar").val('Processar alteração');
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