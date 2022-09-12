<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(); // CRIAR PÁGINA CUSTOMIZADA
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');


$routes->get('login', 'Login::novo');
$routes->get('logout', 'Login::logout');

$routes->get('esqueci', 'Password::esqueci');



// Grupo de rotas para o controller de Formas de pagamentos
$routes->group('formas', function ($routes) {
    $routes->add('/', 'FormasPagamentos::index');
    $routes->add('recuperaformas', 'FormasPagamentos::recuperaFormas');

    $routes->add('exibir/(:segment)', 'FormasPagamentos::exibir/$1');
    $routes->add('editar/(:segment)', 'FormasPagamentos::editar/$1');
    $routes->add('criar/', 'FormasPagamentos::criar');

    // Aqui é POST
    $routes->post('cadastrar', 'FormasPagamentos::cadastrar');
    $routes->post('atualizar', 'FormasPagamentos::atualizar');

    // Aqui é GET e POST
    $routes->match(['get', 'post'], 'excluir/(:segment)', 'FormasPagamentos::excluir/$1');
});


// Grupo de rotas para o controller de Ordens Itens para não dar o erro de 404 - Not found
// quando estiver hospedado
$routes->group('ordensitens', function ($routes) {
    $routes->add('itens/(:segment)', 'OrdensItens::itens/$1');
    $routes->add('pesquisaitens', 'OrdensItens::pesquisaItens');
    $routes->add('adicionaritem', 'OrdensItens::adicionarItem');
    $routes->add('atualizarquantidade/(:segment)', 'OrdensItens::atualizarQuantidade/$1');
    $routes->add('removeritem/(:segment)', 'OrdensItens::removerItem/$1');
});


// Grupo de rotas para o controller de Ordens Evidências para não dar o erro de 404 - Not found
// quando estiver hospedado
$routes->group('ordensevidencias', function ($routes) {
    $routes->add('evidencias/(:segment)', 'OrdensEvidencias::evidencias/$1');
    $routes->add('upload', 'OrdensEvidencias::upload');
    $routes->add('arquivo/(:segment)', 'OrdensEvidencias::arquivo/$1');
    $routes->add('removerevidencia/(:segment)', 'OrdensEvidencias::removerEvidencia/$1');
});

// Rotas para os relatórios
$routes->group('relatorios', function ($routes) {
    $routes->add('produtos-com-estoque-zerado-negativo', 'Relatorios::gerarRelatorioProdutosEstoqueZerado');
    $routes->add('itens-mais-vendidos', 'Relatorios::gerarRelatorioItensMaisVendidos');

    // Rotas das ordens
    $routes->add('ordens-abertas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-encerradas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-excluidas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-canceladas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-aguardando-pagamento', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-nao-pagas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-com-boleto', 'Relatorios::exibeRelatorioOrdens');


    // Contas
    $routes->add('contas-abertas', 'Relatorios::exibeRelatorioContas');
    $routes->add('contas-pagas', 'Relatorios::exibeRelatorioContas');
    $routes->add('contas-vencidas', 'Relatorios::exibeRelatorioContas');


    // Equipe
    $routes->add('desempenho-atendentes', 'Relatorios::exibeRelatorioEquipe');
    $routes->add('desempenho-responsaveis', 'Relatorios::exibeRelatorioEquipe');
});



/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
