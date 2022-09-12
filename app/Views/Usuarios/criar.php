<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-6">

        <div class="block">

            <div class="block-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response">
                

                </div>


                <?php echo form_open('/', ['id' => 'form'], ['id' => "$usuario->id"]) ?>


                <?php echo $this->include('Usuarios/_form'); ?>


                <div class="form-group mt-5 mb-2">


                    <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
                    <a href="<?php echo site_url("usuarios") ?>" class="btn btn-secondary btn-sm ml-2">Voltar</a>

                </div>


                <?php echo form_close(); ?>


            </div>



        </div> <!-- ./ block -->

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>


<script>

$(document).ready(function(){

    $("#form").on('submit', function(e){


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('usuarios/cadastrar'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function(){

                $("#response").html('');
                $("#btn-salvar").val('Por favor aguarde...');

            },
            success: function(response){

                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");

                $('[name=csrf_ordem]').val(response.token);


                if(!response.erro){

                
                    if(response.info){

                        $("#response").html('<div class="alert alert-info">' + response.info + '</div>');

                    }else{

                        // Tudo certo com a atualização do usuário
                        // Podemos agora redirecioná-lo tranquilamente

                        window.location.href = "<?php echo site_url("usuarios/exibir/"); ?>" + response.id;

                    }

                }

                if(response.erro){

                    // Exitem erros de validação


                    $("#response").html('<div class="alert alert-danger">' + response.erro + '</div>');


                    if(response.erros_model){


                        $.each(response.erros_model, function(key, value) {

                            $("#response").append('<ul class="list-unstyled"><li class="text-danger">'+ value +'</li></ul>');

                        });

                    }

                }

            },
            error: function(){

                alert('Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.');
                $("#btn-salvar").val('Salvar');
                $("#btn-salvar").removeAttr("disabled");

            }



        });


    });


    $("#form").submit(function () {

        $(this).find(":submit").attr('disabled', 'disabled');

    });


});


</script>


<?php echo $this->endSection() ?>