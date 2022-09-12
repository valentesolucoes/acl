<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">



    <?php if($forma->id == 1): ?>

    <div class="col-md-12">

        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Importante!</h4>
            <p>A forma de pagamento <b><?php echo esc($forma->nome); ?></b> não pode ser editada ou excluída, pois a mesma será poderá ser associada às ordens de serviços. </p>
            <hr>
            <p class="mb-0">Não se preocupe, pois as demais formas poderão ser editadas ou removidas conforme se fizer
                necessário.</p>
        </div>
    </div>

    <?php endif; ?>



    <?php if($forma->id == 2): ?>

    <div class="col-md-12">

        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Importante!</h4>
            <p>A forma de pagamento <b><?php echo esc($forma->nome); ?></b> não pode ser editada ou excluída, pois a mesma será associada às ordens de serviço que não gerarem valor. </p>
            <hr>
            <p class="mb-0">Não se preocupe, pois as demais formas poderão ser editadas ou removidas conforme se fizer
                necessário.</p>
        </div>
    </div>

    <?php endif; ?>

    <div class="col-lg-3">



        <div class="user-block block">

            <h5 class="card-title mt-2"><?php echo esc($forma->nome); ?></h5>
            <p class="contributions mt-0"><?php echo $forma->exibeSituacao(); ?> </p>
            <p class="card-text"><?php echo esc($forma->descricao); ?></p>
            <p class="card-text">Criado <?php echo $forma->criado_em->humanize(); ?></p>
            <p class="card-text">Atualizado <?php echo $forma->atualizado_em->humanize(); ?></p>


            <!-- Example single danger button -->
            <?php if ($forma->id > 2): ?>

            <div class="btn-group mr-2">
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    Ações
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?php echo site_url("formas/editar/$forma->id"); ?>">
                        Editar forma de pagamento
                    </a>

                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item" href="<?php echo site_url("formas/excluir/$forma->id"); ?>">Excluir forma de pagamento
                    </a>

                </div>

            </div>

            <?php endif; ?>

            <a href="<?php echo site_url("formas") ?>" class="btn btn-secondary">Voltar</a>


        </div> <!-- ./ block -->

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<!-- Aqui coloco os scripts da view-->

<?php echo $this->endSection() ?>