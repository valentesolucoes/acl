<?php

namespace App\Controllers;

use App\Entities\Usuario;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */

class BaseController extends Controller {
    /**
     * Instance of the main Request object.
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form', 'html', 'text', 'autenticacao', 'inflector'];

    /**
     * Constructor.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param LoggerInterface   $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.: $this->session = \Config\Services::session();
    }


    protected function exibeArquivo(string $destino, string $arquivo)    {
        $path = WRITEPATH . "uploads/$destino/$arquivo";

        if(is_file($path) === false){

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o arquivo $arquivo");

        }

        $fileInfo = new \finfo(FILEINFO_MIME);

        $fileType = $fileInfo->file($path);

        $fileSize = filesize($path);

        header("Content-Type: $fileType");

        header("Content-Length: $fileSize");

        readfile($path);
        
        exit;
    }


    /**
     * Retorna o usuário logado
     *
     * @return object
     */
    protected function usuarioLogado(){
        
        return service('autenticacao')->pegaUsuarioLogado();
    }


    /**
     * Registra a ação do usuário logado
     *
     * @param string $texto
     * @return void
     */
    protected function registraAcaoDoUsuario(string $texto){


        $grupo = ($this->usuarioLogado()->is_cliente ? 'Cliente' : 'Usuário');

        $info = [
            'id' => $this->usuarioLogado()->id,
            'nome' => $this->usuarioLogado()->nome,
            'email' => $this->usuarioLogado()->email,
            'ip_address' => $this->request->getIPAddress()
        ];
        
        log_message('info', "[ACAO-USUARIO-ID-{id}] $grupo com o nome {nome} $texto com o e-mail {email} e com IP {ip_address}", $info);

    }

}
