<!DOCTYPE html>
<html>
  
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> Ordem de Serviço | <?php echo $this->renderSection('titulo'); ?> </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>vendor/font-awesome/css/font-awesome.min.css">
    <!-- Custom Font Icons CSS-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>css/font.css">
    <!-- Google fonts - Muli-->
    <link rel="stylesheet"
        href="<?php echo site_url('recursos/'); ?>https://fonts.googleapis.com/css?family=Muli:300,400,700">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo site_url('recursos/'); ?>css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="<?php echo site_url('recursos/'); ?>img/favicon.ico">
    <!-- Tweaks for older IEs-->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <!-- Espaço reservado para renderizar os estilos de cada view  que estender esse layout -->
    <?php echo $this->renderSection('estilos'); ?>

</head>

  <body>
    <div class="login-page">

      <div class="container d-flex align-items-center">
        <div class="form-holder has-shadow">


          <?php echo $this->include('Layout/_mensagens'); ?>


          <!-- Espaço reservado para renderizar o conteúdo de cada view  que estender esse layout -->
          <?php echo $this->renderSection('conteudo'); ?>

        </div>
      </div>
      <div class="copyrights text-center">
         <p>2018 &copy; Your company. Download From <a target="_blank" href="https://templateshub.net">Templates Hub</a>.</p>
      </div>
    </div>
    <!-- JavaScript files-->

    <!-- JavaScript files-->
    <script src="<?php echo site_url('recursos/'); ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/popper.js/umd/popper.min.js"> </script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>js/front.js"></script>

    <!-- Espaço reservado para renderizar os scripts de cada view que estender esse layout -->
    <?php echo $this->renderSection('scripts'); ?>
 
  </body>
</html>