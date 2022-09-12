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


                <?php echo form_open("ordens/excluir/$ordem->codigo") ?>


                <div class="alert alert-warning" role="alert">
                    Tem certeza da exclusão do registro?
                </div>


                <div class="form-group mt-5 mb-2">

                    <input id="btn-salvar" type="submit" value="Sim, pode excluir" class="btn btn-danger btn-sm mr-2">
                    <a href="<?php echo site_url("ordens/detalhes/$ordem->codigo") ?>"
                        class="btn btn-secondary btn-sm ml-2">Cancelar</a>

                </div>


                <?php echo form_close(); ?>


            </div>



        </div> <!-- ./ block -->

    </div>


</div>


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>


<?php echo $this->endSection() ?>