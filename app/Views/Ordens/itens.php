<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>


<link rel="stylesheet" href="<?php echo site_url("/recursos/vendor/auto-complete/jquery-ui.css"); ?>">


<style>
/* 
    Para o autocomplete não ficar atrás do modal.
    Também definimos um tamanho máximo para a lista de resultados do autocomplete.
    Dessa forma não estrapola o layout
*/
.ui-autocomplete {
    max-height: 300px;
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
    z-index: 9999 !important;
    /* Para o autocomplete não ficar atrás do modal */
}


/* Muda o backgroud do autocomplete */
.ui-menu-item .ui-menu-item-wrapper.ui-state-active {
    background: #fff !important;
    color: #007bff !important;
    border: none;
}
</style>


<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-12">

        <div class="block">


            <!-- Button trigger modal -->
            <button type="button" class="btn btn-outline-secondary btn-lg" data-toggle="modal"
                data-target="#exampleModal">
                Adicionar itens
            </button>



            <?php if ($ordem->itens === null): ?>

            <div class="user-block text-center">

                <div class="contributions pt-3">

                    <p>Nenhum item foi adicionando à ordem</p>

                </div>

            </div>


            <?php else: ?>


            <div class="table-responsive my-5">

                <table class="table table-borderless table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Item</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Preço</th>
                            <th scope="col">Qtde</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col" class="text-center">Remover</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                    
                            $valorProdutos = 0;
                            $valorServicos = 0;
                    
                        ?>

                        <?php foreach ($ordem->itens as $item): ?>

                        <?php
                            
                            if ($item->tipo === 'produto') {
                                $valorProdutos += $item->preco_venda * $item->item_quantidade;
                            } else {
                                $valorServicos += $item->preco_venda * $item->item_quantidade;
                            }

                            $hiddenAcoes = [
                                'id_principal' => $item->id_principal,
                                'item_id' => $item->id,
                            ];
                            
                        ?>

                        <tr>
                            <th scope="row"><?php echo ellipsize($item->nome, 32, .5); ?></th>
                            <td><?php echo esc(ucfirst($item->tipo)); ?></td>
                            <td>R$ <?php echo esc(number_format($item->preco_venda, 2)); ?></td>
                            <td>

                                <?php echo form_open("ordensitens/atualizarquantidade/$ordem->codigo", ['class' => 'form-inline'], $hiddenAcoes); ?>


                                <input style="max-width: 80px !important" type="number" name="item_quantidade"
                                    class="form-control form-control-sm" value="<?php echo $item->item_quantidade; ?>" required>

                                <button type="submit" class="btn btn-outline-success btn-sm ml-2">
                                    <i class="fa fa-refresh"></i>
                                </button>

                                <?php echo form_close(); ?>
                            </td>

                            <td>R$ <?php echo esc(number_format($item->item_quantidade * $item->preco_venda, 2)); ?></td>

                            <td class="pt-2 text-center">

                                <?php
                            
                                    $atributosRemover = [
                                        'class' => 'form-inline',
                                        'onClick' => 'return confirm("Tem certeza da exclusão?")',
                                    ];
                                
                                ?>


                                <?php echo form_open("ordensitens/removeritem/$ordem->codigo", $atributosRemover, $hiddenAcoes); ?>


                                

                                <button type="submit" class="btn btn-outline-danger btn-sm ml-2 mx-auto">
                                    <i class="fa fa-times"></i>
                                </button>

                                <?php echo form_close(); ?>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                    <tfoot>

                        <tr>

                            <td class="text-right font-weight-bold" colspan="4">

                                <label>Valor produtos:</label>
                                
                            </td>

                            <td class="font-weight-bold">R$ <?php echo esc(number_format($valorProdutos, 2)); ?></td>


                        </tr>

                        <tr>

                            <td class="text-right font-weight-bold" colspan="4">

                                <label>Valor serviços:</label>
                                
                            </td>

                            <td class="font-weight-bold">R$ <?php echo esc(number_format($valorServicos, 2)); ?></td>


                        </tr>

                        <tr>

                            <td class="text-right font-weight-bold" colspan="4">

                                <label>Valor total:</label>
                                
                            </td>

                            <td class="font-weight-bold">R$ <?php echo esc(number_format($valorServicos + $valorProdutos, 2)); ?></td>


                        </tr>


                    </tfoot>

                </table>

            </div>


            <?php endif; ?>


            <!-- Example single danger button -->
            <div class="btn-group">
                <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    Ações
                </button>
                <div class="dropdown-menu">

                    <?php if ($ordem->situacao === 'aberta'): ?>

                    <a class="dropdown-item" href="<?php echo site_url("ordens/editar/$ordem->codigo"); ?>">Editar
                        ordem</a>

                    <a class="dropdown-item" href="<?php echo site_url("ordens/encerrar/$ordem->codigo"); ?>">Encerrar
                        ordem</a>

                    <a class="dropdown-item"
                        href="<?php echo site_url("ordensitens/itens/$ordem->codigo"); ?>">Gerenciar itens da ordem</a>

                    <a class="dropdown-item" href="<?php echo site_url("ordens/responsavel/$ordem->codigo"); ?>">Definir
                        técnico responsável</a>

                    <?php endif; ?>


                    <a class="dropdown-item"
                        href="<?php echo site_url("ordensevidencias/evidencias/$ordem->codigo"); ?>">Evidências da
                        ordem</a>

                    <a class="dropdown-item" href="<?php echo site_url("ordens/email/$ordem->codigo"); ?>">Enviar por
                        e-mail</a>

                    <a target="_blank" class="dropdown-item" href="<?php echo site_url("ordens/gerarpdf/$ordem->codigo"); ?>">Gerar
                        PDF</a>

                    <div class="dropdown-divider"></div>

                    <?php if ($ordem->deletado_em === null): ?>

                    <a class="dropdown-item" href="<?php echo site_url("ordens/excluir/$ordem->codigo"); ?>">Excluir
                        ordem</a>

                    <?php else: ?>

                    <a class="dropdown-item"
                        href="<?php echo site_url("ordens/desfazerexclusao/$ordem->codigo"); ?>">Restaurar ordem</a>

                    <?php endif; ?>

                </div>
            </div>

            <a href="<?php echo site_url("ordens/detalhes/$ordem->codigo") ?>" class="btn btn-secondary btn-sm ml-2">Voltar</a>


        </div> <!-- ./ block -->

    </div>




</div>




<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Adicionar itens na ordem
                    <?php echo $ordem->codigo; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="response">

                </div>


                <div class="ui-widget">

                    <input type="text" name="query" id="query" class="form-control form-control-lg mb-5"
                        placeholder="Pesquise pelo nome ou código do item">

                </div>


                <div class="block-body">



                    <?php

                        $hiddens = [
                            'codigo' => $ordem->codigo,
                            'item_id' => '', // será preenchido quando o item for escolhido no autocomplete
                        ];
                    
                    ?>


                    <?php echo form_open('/', ['id' => 'form'], $hiddens) ?>


                    <div class="form-row">


                        <div class="form-group col-md-8">

                            <label class="form-control-label">Item</label>
                            <input type="text" name="item_nome" class="form-control" readonly required>

                        </div>

                        <div class="form-group col-md-2">

                            <label class="form-control-label">Valor</label>
                            <input type="text" name="item_preco" class="form-control money" readonly required>

                        </div>

                        <div class="form-group col-md-2">

                            <label class="form-control-label">Qtde</label>
                            <input type="number" name="item_quantidade" class="form-control" value="1" min="1" step="1"
                                required>

                        </div>


                    </div>


                    <div class="form-group mt-5">

                        <input id="btn-salvar" type="submit" value="Salvar" class="btn btn-danger btn-sm mr-2">

                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>

                    </div>



                    <?php echo form_close(); ?>


                </div>



            </div>
        </div>
    </div>
</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url("recursos/vendor/auto-complete/jquery-ui.js"); ?>"></script>
<script src="<?php echo site_url("recursos/vendor/mask/jquery.mask.min.js"); ?>"></script>
<script src="<?php echo site_url("recursos/vendor/mask/app.js"); ?>"></script>


<script>
$(document).ready(function() {


    $(function() {



        $("#query").autocomplete({

            minLength: 4, // Tamanho mínimo de 4 caractéres para começar a pesquisar
            source: function(request, response) {

                $.ajax({

                    url: "<?php echo site_url('ordensitens/pesquisaitens');?>",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    beforeSend: function() {

                        $("#response").html('');

                        // Limpamos o form do modal
                        $("#form")[0].reset();
                    },
                    success: function(data) {

                        if (data.length < 1) {

                            var data = [{

                                label: 'Item não encontrado',
                                value: -1,
                            }];

                        } // fim if

                        response(data);


                    }, // fim success

                }); // fim ajax

            }, // fim souce

            select: function(event, ui) {

                $(this).val("");

                event.preventDefault();

                if (ui.item.value == -1) {

                    // Nenhum item foi encontrado

                    // Limpamos o input de pesquisa e retornamos false para não redirecionar para página 404
                    $(this).val("");
                    return false;

                } else {

                    // Aqui pelo menos um item foi encontrado na base

                    var item_id = ui.item.id;
                    var item_nome = ui.item.value;
                    var item_preco = ui.item.item_preco;


                    // Preenchemos os inputs names com os valores das variáveis acima
                    $("[name=item_id]").val(item_id);
                    $("[name=item_nome]").val(item_nome);
                    $("[name=item_preco]").val(item_preco);



                } // fim else

            }, // fim select

        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li class='ui-autocomplete-row'></li>")
                .data("item.autocomplete", item)
                .append(item.label)
                .appendTo(ul);
        }; // fim autocomplete

    }); // fim function



    $("#form").on('submit', function(e) {


        e.preventDefault();


        $.ajax({

            type: 'POST',
            url: '<?php echo site_url('ordensitens/adicionaritem'); ?>',
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


                    if (response.info) {

                        $("#response").html('<div class="alert alert-info">' + response
                            .info + '</div>');

                    } else {

                        // Tudo certo com a atualização do usuário
                        // Podemos agora redirecioná-lo tranquilamente

                        window.location.href =
                            "<?php echo site_url("ordensitens/itens/$ordem->codigo"); ?>";

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