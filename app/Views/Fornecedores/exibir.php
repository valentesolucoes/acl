<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div class="row">

    <div class="col-lg-4">

        <div class="user-block block">

        
            <h5 class="card-title mt-2"><?php echo esc($fornecedor->razao); ?></h5>
            <p class="card-text">CNPJ: <?php echo esc($fornecedor->cnpj); ?></p>
            <p class="card-text">Telefone: <?php echo esc($fornecedor->telefone); ?></p>
            <p class="contributions mt-0"><?php echo $fornecedor->exibeSituacao(); ?></p>
            <p class="card-text">Criado <?php echo $fornecedor->criado_em->humanize(); ?></p>
            <p class="card-text">Atualizado <?php echo $fornecedor->atualizado_em->humanize(); ?></p>
            

            <!-- Example single danger button -->
            <div class="btn-group">
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    Ações
                </button>
                <div class="dropdown-menu">

                    <a class="dropdown-item" href="<?php echo site_url("fornecedores/editar/$fornecedor->id"); ?>">Editar fornecedor</a>
                    <a class="dropdown-item" href="<?php echo site_url("fornecedores/notas/$fornecedor->id"); ?>">Gerenciar as notas fiscais</a>
                   
                    <div class="dropdown-divider"></div>

                    <?php if ($fornecedor->deletado_em == null): ?>
                    
                        <a class="dropdown-item" href="<?php echo site_url("fornecedores/excluir/$fornecedor->id"); ?>">Excluir fornecedor</a>
                    
                    <?php else: ?>

                        <a class="dropdown-item" href="<?php echo site_url("fornecedores/desfazerexclusao/$fornecedor->id"); ?>">Restaurar fornecedor</a>

                    <?php endif; ?>

                </div>
            </div>

            <a href="<?php echo site_url("fornecedores") ?>" class="btn btn-secondary ml-2">Voltar</a>


        </div> <!-- ./ block -->

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<!-- Aqui coloco os scripts da view-->

<?php echo $this->endSection() ?>