<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>  

<!-- Aqui coloco os estilos da view-->

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>  

<!-- Aqui coloco o conteudo da view-->


<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>  

<!-- Aqui coloco os scripts da view-->

<?php echo $this->endSection() ?>