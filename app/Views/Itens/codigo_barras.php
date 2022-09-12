<?php echo $this->extend('Layout/Autenticacao/principal_autenticacao'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>  

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>  

<!-- Aqui coloco o conteudo da view-->

  <div class="row">
    <!-- Logo & Information Panel-->
    <div class="col-lg-6 mx-auto">
      <div class="form d-flex align-items-center bg-info">
        <div class="content">
          <div class="mt-5 text-center text-white">

            <p><?php echo $item->codigo_barras; ?></p>
            <p><?php echo $item->codigo_interno; ?></p>
            <p><?php echo $item->nome; ?></p>
            
            <p><button class="btn btn-primary bg-dark" onclick="window.print();">Imprimir</button></p>

          </div>
        </div>
      </div>
    </div>
  
  </div>


<?php echo $this->endSection() ?>



<?php echo $this->section('scripts') ?>  



<?php echo $this->endSection() ?>
