<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-4">

        <div class="user-block block">


            <h5 class="card-title mt-2"><?php echo esc($cliente->nome); ?></h5>
            <p class="card-text">CPF: <?php echo esc($cliente->cpf); ?></p>
            <p class="card-text">Telefone: <?php echo esc($cliente->telefone); ?></p>
            <p class="contributions mt-0"><?php echo $cliente->exibeSituacao(); ?></p>
            <p class="card-text">Criado <?php echo $cliente->criado_em->humanize(); ?></p>
            <p class="card-text">Atualizado <?php echo $cliente->atualizado_em->humanize(); ?></p>


            <a href="<?php echo site_url("clientes/exibir/$cliente->id") ?>" class="btn btn-secondary ml-2">Voltar</a>


        </div> <!-- ./ block -->

    </div>

    <div class="col-lg-8">

        <div class="user-block block">

            <?php if( ! isset($ordensCliente)): ?>

            <div class="contributions text-center text-warning">
                Esse cliente não possui histórico de atendimento
            </div>

            <?php else: ?>

            <div id="accordion">

                <?php foreach($ordensCliente as $key => $ordem): ?>

                <div class="card">
                    <div class="card-header" id="heading-<?php echo $key; ?>">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse"
                                data-target="#collapse-<?php echo $key; ?>" aria-expanded="true"
                                aria-controls="collapseOne">
                                Atendimento realizado em <?php echo date('d/m/Y H:i', strtotime($ordem->criado_em)) ?>
                            </button>
                        </h5>
                    </div>

                    <div id="collapse-<?php echo $key; ?>" class="collapse <?php echo($key === 0 ? 'show' : ''); ?>"
                        aria-labelledby="heading-<?php echo $key; ?>" data-parent="#accordion">
                        <div class="card-body">

                            <p><strong>Código ordem:</strong>&nbsp;<?php echo $ordem->codigo; ?></p>
                            <p><strong>Situação:</strong>&nbsp;<?php echo $ordem->exibeSituacao(); ?></p>
                            <p><strong>Equipamento:</strong>&nbsp;<?php echo $ordem->equipamento; ?></p>
                            <p><strong>Defeito:</strong>&nbsp;<?php echo ($ordem->defeito != null ? $ordem->defeito : 'Não informado'); ?></p>
                            <p><strong>Observações:</strong>&nbsp;<?php echo ($ordem->observacoes != null ? $ordem->observacoes : 'Não informado'); ?></p>

                            <a target="_blank" class="btn btn-outline-info text-white btn-sm" href="<?php echo site_url("ordens/detalhes/$ordem->codigo"); ?>">Mais detalhes</a>

                        </div>
                    </div>
                </div>

                <?php endforeach; ?>

            </div>

            <?php endif; ?>

        </div>

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<!-- Aqui coloco os scripts da view-->

<?php echo $this->endSection() ?>