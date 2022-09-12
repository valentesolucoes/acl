<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link rel="stylesheet" type="text/css"
    href="<?php echo site_url('recursos/vendor/selectize/selectize.bootstrap4.css') ?>" />

<style>
/* Estilizando o select para acompanhar a formatação do template */

.selectize-input,
.selectize-control.single .selectize-input.input-active {
    background: #2d3035 !important;
}

.selectize-dropdown,
.selectize-input,
.selectize-input input {
    color: #777;
}

.selectize-input {
    /*        height: calc(2.4rem + 2px);*/
    border: 1px solid #444951;
    border-radius: 0;
}
</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-8">

        <div class="block">

            <div class="block-body">

                <!-- Exibirá os retornos do backend -->
                <div id="response">


                </div>


                <?php echo form_open('/', ['id' => 'form'], ['codigo' => "$ordem->codigo"]) ?>


                <?php echo $this->include('Ordens/_form'); ?>


                <div class="form-group mt-5 mb-2">


                    <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">
                    <a href="<?php echo site_url("ordens") ?>" class="btn btn-secondary btn-sm ml-2">Voltar</a>

                </div>


                <?php echo form_close(); ?>


            </div>



        </div> <!-- ./ block -->

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script type="text/javascript" src="<?php echo site_url('recursos/vendor/selectize/selectize.min.js') ?>"></script>


<script>
$(document).ready(function() {

    var $select = $(".selectize").selectize({
        create: false,
        // sortField: "text",

        maxItem: 1,
        valueField: 'id',
        labelField: 'nome',
        searchField: ['nome', 'cpf'],

        load: function(query, callback) {

            if (query.length < 4) {

                return callback();

            }

            $.ajax({

                url: '<?php echo site_url("ordens/buscaclientes/") ?>',
                data: {
                    termo: encodeURIComponent(query)
                },

                success: function(response) {

                    $select.options = response;

                    callback(response);

                },
                error: function() {

                    alert(
                        'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.'
                    );


                }

            });


        }

    }); // fim selectize



    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('ordens/cadastrar'); ?>',
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


                    window.location.href =
                        "<?php echo site_url("ordens/detalhes/"); ?>" + response.codigo;

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
                    'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.');
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