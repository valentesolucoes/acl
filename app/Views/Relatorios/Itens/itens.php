<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-6">

        <div id="accordion">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                            aria-expanded="true" aria-controls="collapseOne">
                            Produtos com estoque zerado ou negativo
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        Possibilita a geração de relatório em PDF de itens do tipo produto que estejam com o estoque
                        zerado ou negativo.
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo site_url('relatorios/produtos-com-estoque-zerado-negativo'); ?>"
                            target="_blank" class="btn btn-dark btn-sm text-secondary">Gerar relatórios</a>
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo"
                            aria-expanded="false" aria-controls="collapseTwo">
                            Itens mais vendidos
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="card-body">
                        Possibilita a geração de relatório em PDF dos itens que foram mais vendidos, tendo em mente que
                        serão comtempladas apenas as Ordens de Serviço que estejam com o status de Encerrada


                        <?php echo form_open('/', ['id' => 'form']); ?>

                        <div id="response">

                        </div>

                        <div class="form-row">

                            <div class="form-group col-lg-12 mt-3">

                                <label class="form-control-label">Tipo de item</label>

                                <select class="custom-select" name="tipo">

                                    <option value="">Escolha o tipo....</option>
                                    <option value="produto">Produto</option>
                                    <option value="serviço">Serviço</option>

                                </select>

                            </div>

                            <div class="form-group col-lg-6">

                                <label class="form-control-label">Data inicial</label>
                                <input type="datetime-local" name="data_inicial" class="form-control">

                            </div>

                            <div class="form-group col-lg-6">

                                <label class="form-control-label">Data final</label>
                                <input type="datetime-local" name="data_final" class="form-control">

                            </div>


                            <div class="form-group col-lg-6 mt-2">

                                <input id="btn-mais-vendidos" type="submit" value="Gerar relatório" class="btn btn-dark btn-sm text-secondary">

                            </div>


                        </div>


                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>


        </div>


    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script>
$(document).ready(function() {

    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('relatorios/itensmaisvendidos'); ?>',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {

                $("#response").html('');
                $("#btn-mais-vendidos").val('Por favor aguarde...');

            },
            success: function(response) {

                $("#btn-mais-vendidos").val('Salvar');
                $("#btn-mais-vendidos").removeAttr("disabled");

                $('[name=csrf_ordem]').val(response.token);


                if (!response.erro) {


                    if (response.info) {

                        $("#response").html('<div class="alert alert-info">' + response
                            .info + '</div>');

                    } else {

                        // Tudo certo com a atualização do usuário
                        // Podemos agora redirecioná-lo tranquilamente

                        

                        var url = "<?php echo site_url(); ?>" + response.redirect;

                        var win = window.open(url, '_blank');
                        win.focus();


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

                alert(
                    'Não foi possível procesar a solicitação. Por favor entre em contato com o suporte técnico.');
                $("#btn-mais-vendidos").val('Salvar');
                $("#btn-mais-vendidos").removeAttr("disabled");

            }



        });


    });


    $("#form").submit(function() {

        $(this).find(":submit").attr('disabled', 'disabled');

    });


});
</script>

<?php echo $this->endSection() ?>