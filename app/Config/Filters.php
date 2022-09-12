<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
	/**
	 * Configures aliases for Filter classes to
	 * make reading things nicer and simpler.
	 *
	 * @var array
	 */
	public $aliases = [
		'csrf'     => CSRF::class,
		'toolbar'  => DebugToolbar::class,
		'honeypot' => Honeypot::class,
		'login'    => \App\Filters\LoginFilter::class, // Filtro de login
		'visitante'    => \App\Filters\VisitanteFilter::class, // Filtro visitante
		'cliente'    => \App\Filters\ClienteFilter::class, // Filtro cliente
	];

	/**
	 * List of filter aliases that are always
	 * applied before and after every request.
	 *
	 * @var array
	 */
	public $globals = [
		'before' => [
			// 'honeypot',
			'csrf' => ['except' => 'transacoes/notificacoes'],
		],
		'after'  => [
			'toolbar',
			// 'honeypot',
		],
	];

	/**
	 * List of filter aliases that works on a
	 * particular HTTP method (GET, POST, etc.).
	 *
	 * Example:
	 * 'post' => ['csrf', 'throttle']
	 *
	 * @var array
	 */
	public $methods = [];

	/**
	 * List of filter aliases that should run on any
	 * before or after URI patterns.
	 *
	 * Example:
	 * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
	 *
	 * @var array
	 */
	public $filters = [
		'login' => [
			'before' => [
				'/',
				'home(/*)?',
				'usuarios(/*)?',
				'grupos(/*)?',
				'fornecedores(/*)?',
				'itens(/*)?',
				'formasPagamentos(/*)?',
				'eventos(/*)?',
				'ordens(/*)?',
				'contas(/*)?',
				'formas(/*)?',
				'ordensitens(/*)?',
				'ordensevidencias(/*)?',
				'transacoes/editar',
				'transacoes/atualizar',
				'transacoes/cancelar',
				'transacoes/consultar',
				'transacoes/pagar',

			],
		],
		'visitante' => [
			'before' => [
				'login(/*)?',
				'password(/*)?',
			],
		],
		'cliente' => [
			'before' => [
				'ordens/minhas',
				'ordens/exibirordemcliente(/*)?',
			],
		],
	];
}
