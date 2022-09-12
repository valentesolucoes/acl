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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">
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
    <header class="header">
        <nav class="navbar navbar-expand-lg">
            <div class="search-panel">
                <div class="search-inner d-flex align-items-center justify-content-center">
                    <div class="close-btn">Close <i class="fa fa-close"></i></div>
                    <form id="searchForm" action="#">
                        <div class="form-group">
                            <input type="search" name="search" placeholder="What are you searching for...">
                            <button type="submit" class="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <div class="navbar-header">
                    <!-- Navbar Header--><a href="index.html" class="navbar-brand">
                        <div class="brand-text brand-big visible text-uppercase"><strong
                                class="text-primary">Dark</strong><strong>Admin</strong></div>
                        <div class="brand-text brand-sm"><strong class="text-primary">D</strong><strong>A</strong></div>
                    </a>
                    <!-- Sidebar Toggle Btn-->
                    <button class="sidebar-toggle"><i class="fa fa-long-arrow-left"></i></button>
                </div>
                <div class="right-menu list-inline no-margin-bottom">

                    <!-- Log out               -->
                    <div class="list-inline-item logout">
                        <a id="logout" href="<?php echo site_url('logout'); ?>" class="nav-link">Sair <i
                                class="icon-logout"></i></a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <div class="d-flex align-items-stretch">
        <!-- Sidebar Navigation-->
        <nav id="sidebar">
            <!-- Sidebar Header-->
            <div class="sidebar-header d-flex align-items-center">
                <div class="avatar">

                    <?php if(usuario_logado()->imagem === null): ?>

                    <img src="<?php echo site_url('recursos/img/usuario_sem_imagem.png'); ?>"
                        alt="<?php echo esc(usuario_logado()->nome); ?>" class="img-fluid rounded-circle">

                    <?php else: ?>

                    <img src="<?php echo site_url('usuarios/imagem/'. usuario_logado()->imagem); ?>"
                        alt="<?php echo esc(usuario_logado()->nome); ?>" class="img-fluid rounded-circle">

                    <?php endif; ?>
                </div>
                <div class="title">
                    <h1 class="h5"><?php echo esc(usuario_logado()->nome); ?></h1>

                    <?php if(usuario_logado()->is_admin): ?>
                    <p>Administrador</p>
                    <?php endif; ?>

                    <?php if(usuario_logado()->is_cliente): ?>
                    <p>Cliente</p>
                    <?php endif; ?>


                </div>
            </div>
            <!-- Sidebar Navidation Menus--><span class="heading">Main</span>
            <ul class="list-unstyled">

                <li class="<?php echo (url_is('/') ? 'active' : '') ?>"><a href="<?php echo site_url('/'); ?>"> <i
                            class="icon-home"></i>Home </a></li>


                <?php if(usuario_logado()->is_admin || usuario_logado()->is_cliente): ?>

                <li class="<?php echo (url_is('ordens*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('ordens/minhas'); ?>"> <i class="icon-list"></i>Minhas ordens </a>
                </li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-ordens')): ?>

                <li class="<?php echo (url_is('ordens*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('ordens'); ?>"> <i class="icon-list"></i>Ordens de serviço </a></li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-itens')): ?>

                <li class="<?php echo (url_is('itens*') ? 'active' : '') ?>"><a href="<?php echo site_url('itens'); ?>">
                        <i class="icon-list"></i>Itens </a></li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-clientes')): ?>

                <li class="<?php echo (url_is('clientes*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('clientes'); ?>"> <i class="icon-user"></i>Clientes </a></li>

                <?php endif; ?>

                <?php if(usuario_logado()->temPermissaoPara('listar-fornecedores')): ?>

                <li class="<?php echo (url_is('fornecedores*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('fornecedores'); ?>"> <i class="icon-user"></i>Fornecedores </a></li>

                <?php endif; ?>

                <?php if(usuario_logado()->temPermissaoPara('listar-contas')): ?>

                <li class="<?php echo (url_is('contas*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('contas'); ?>"> <i class="icon-user"></i>Contas a pagar </a></li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-formas')): ?>

                <li class="<?php echo (url_is('formas*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('formas'); ?>"> <i class="icon-padnote"></i>Formas de pagamentos</a>
                </li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-eventos')): ?>

                <li class="<?php echo (url_is('eventos*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('eventos'); ?>"> <i class="icon-chart"></i>Eventos </a></li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-usuarios')): ?>

                <li class="<?php echo (url_is('usuarios*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('usuarios'); ?>"> <i class="icon-user"></i>Usuários </a></li>


                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('listar-grupos')): ?>

                <li class="<?php echo (url_is('grupos*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('grupos'); ?>"> <i class="icon-settings"></i>Grupos & Permissões </a>
                </li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('visualizar-relatorios')): ?>


                <li class="<?php echo (url_is('relatorios*') ? 'active' : '') ?>"><a href="#exampledropdownDropdown"
                        aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Relatórios </a>
                    <ul id="exampledropdownDropdown" class="collapse list-unstyled ">
                        <li><a href="<?php echo site_url('relatorios/itens'); ?>">Itens</a></li>
                        <li><a href="<?php echo site_url('relatorios/ordens'); ?>">Ordens de serviço</a></li>
                        <li><a href="<?php echo site_url('relatorios/contas'); ?>">Contas de fornecedores</a></li>
                        <li><a href="<?php echo site_url('relatorios/equipe'); ?>">Desempenho da equipe</a></li>
                    </ul>
                </li>

                <?php endif; ?>


                <?php if(usuario_logado()->temPermissaoPara('visualizar-logs')): ?>

                <li class="<?php echo (url_is('logs*') ? 'active' : '') ?>"><a
                        href="<?php echo site_url('logs'); ?>"> <i class="icon-settings"></i>Analisar Logs </a>
                </li>

                <?php endif; ?>


                <li><a href="<?php echo site_url("usuarios/editarsenha"); ?>"> <i class="icon-settings"></i>Alterar
                        senha </a></li>



        </nav>
        <!-- Sidebar Navigation end-->
        <div class="page-content">
            <div class="page-header">
                <div class="container-fluid">
                    <h2 class="h5 no-margin-bottom"><?php echo $titulo; ?></h2>
                </div>
            </div>
            <section class="no-padding-top no-padding-bottom">


                <div class="container-fluid">

                    <?php echo $this->include('Layout/_mensagens'); ?>

                    <!-- Espaço reservado para renderizar o conteúdo de cada view  que estender esse layout -->
                    <?php echo $this->renderSection('conteudo'); ?>

                </div>


            </section>


            <footer class="footer">
                <div class="footer__block block no-margin-bottom">
                    <div class="container-fluid text-center">
                        <!-- Please do not remove the backlink to us unless you support us at https://bootstrapious.com/donate. It is part of the license conditions. Thank you for understanding :)-->
                        <p class="no-margin-bottom"><?php echo date('Y'); ?> &copy; Your company. Download From <a
                                target="_blank" href="https://templateshub.net">Templates Hub</a>.</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- JavaScript files-->
    <script src="<?php echo site_url('recursos/'); ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/popper.js/umd/popper.min.js"> </script>
    <script src="<?php echo site_url('recursos/'); ?>vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo site_url('recursos/'); ?>js/front.js"></script>

    <!-- Espaço reservado para renderizar os scripts de cada view que estender esse layout -->
    <?php echo $this->renderSection('scripts'); ?>


    <?php if(url_is('eventos*')): ?>

    <!-- Para o fullcalendar funcionar, preciso carregar os mesmos scripts que tenho na view-->
    <!-- No entanto, eles serão carregados apenas quando estiver no controller Eventos -->
    <script src="<?php echo site_url('recursos/vendor/fullcalendar/fullcalendar.min.js'); ?>"></script>
    <script src="<?php echo site_url('recursos/vendor/fullcalendar/toastr.min.js'); ?>"></script>
    <script src="<?php echo site_url('recursos/vendor/fullcalendar/moment.min.js'); ?>"></script>
    <?php endif ?>

    <script>
    $(function() {
        $('[data-toggle="popover"]').popover({
            html: true,
        });
    })
    </script>

</body>

</html>