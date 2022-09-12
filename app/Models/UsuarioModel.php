<?php

namespace App\Models;

use CodeIgniter\Model;

use App\Libraries\Token;
use CodeIgniter\Database\BaseBuilder;

class UsuarioModel extends Model
{
    protected $table                = 'usuarios';
    protected $returnType           = 'App\Entities\Usuario';
    protected $useSoftDeletes       = true; // Explicar essa característica
    protected $allowedFields        = [
        'nome',
        'email',
        'password',
        'reset_hash',
        'reset_expira_em',
        'imagem',
        // Não colocaremos o campo ativo.... Pois existe a manipulação de formulário
    ];

    // Dates
    protected $useTimestamps        = true;
    protected $createdField         = 'criado_em';
    protected $updatedField         = 'atualizado_em';
    protected $deletedField         = 'deletado_em';

    // Validation
    protected $validationRules    = [
        'nome'         => 'required|min_length[3]|max_length[125]',
        'email'        => 'required|valid_email|max_length[230]|is_unique[usuarios.email,id,{id}]', // Não pode ter espaços
        'password'     => 'required|min_length[6]',
        'password_confirmation' => 'required_with[password]|matches[password]'
    ];

    protected $validationMessages = [
        'nome'        => [
            'required' => 'O campo Nome é obrigatório.',
            'min_length' => 'O campo Nome precisa ter pelo menos 3 caractéres.',
            'max_length' => 'O campo Nome não pode ser maior que 125 caractéres.',
        ],
        'email'        => [
            'required' => 'O campo E-mail é obrigatório.',
            'max_length' => 'O campo Nome não pode ser maior que 230 caractéres.',
            'is_unique' => 'Esse e-mail já foi escolhido. Por favor informe outro.'
        ],
        'password_confirmation'        => [
            'required_with' => 'Por favor confirme a sua senha.',
            'matches' => 'As senhas precisam combinar.',
        ],
    ];


    // Callbacks
    protected $beforeInsert         = ['hashPassword'];
    protected $beforeUpdate         = ['hashPassword'];



    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

            // Removemos dos dados a serem salvos
            unset($data['data']['password']);
            unset($data['data']['password_confirmation']);
        }

        return $data;
    }


    /**
     * Método que recupera o usuário para logar na aplicação
     *
     * @param string $email
     * @return null|object
     */
    public function buscaUsuarioPorEmail(string $email)
    {
        return $this->where('email', $email)->where('deletado_em', null)->first();
    }

    /**
     * Método que recupera as permissões do usuário logado
     *
     * @param integer $usuario_id
     * @return null|array
     */
    public function recuperaPermissoesDoUsuarioLogado(int $usuario_id)
    {
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        $atributos = [
            // 'usuarios.id',
            // 'usuarios.nome AS usuario',
            // 'grupos_usuarios.*',
            'permissoes.nome AS permissao',
        ];


        return $this->select($atributos)
                    ->asArray() // Recuperamos no formato array
                    ->join('grupos_usuarios', 'grupos_usuarios.usuario_id = usuarios.id')
                    ->join('grupos_permissoes', 'grupos_permissoes.grupo_id = grupos_usuarios.grupo_id')
                    ->join('permissoes', 'permissoes.id = grupos_permissoes.permissao_id')
                    ->where('usuarios.id', $usuario_id)
                    ->groupBy('permissoes.nome')
                    ->findAll();
    }


    /**
     * Método que recupera o usuário de acordo com o hash do token.
     *
     * @param string $token
     * @return null|object
     */
    public function buscaUsuarioParaRedefinirSenha(string $token)
    {

        // Instanciando o objeto da classe, passando como parâmetro no contrutor o $token
        $token = new Token($token);


        // Recuperando o hash do token
        $tokenHash = $token->getHash();

        
        // Consultando na base o usuário de acordo com hash
        $usuario = $this->where('reset_hash', $tokenHash)
                       ->where('deletado_em', null)
                       ->first();


        // Validamos se o usuário foi encontrado
        if ($usuario === null) {
            return null;
        }


        // Validamos se o token ainda é valido (não expirou)
        if ($usuario->reset_expira_em < date('Y-m-d H:i:s')) {
            return null;
        }


        // Nesse ponto, está tudo certo. Usuário existe e o token é válido
        return $usuario;
    }


    /**
     * Método que atualiza o e-mail do usuário de acordo com o email do cliente
     *
     * @param integer $usuario_id
     * @param string $email
     * @return void
     */
    public function atualizaEmailDoCliente(int $usuario_id, string $email)
    {
        return $this->protect(false)
                    ->where('id', $usuario_id)
                    ->set('email', $email)
                    ->update();
    }


    /**
     * Método responsável por recuperar o técnicos para serem exibidos como opções de definição do mesmo para a ordem de serviço
     * @param string $termo
     *
     * @return null|array
     */
    public function recuperaResponsaveisParaOrdem(string $termo = null)
    {
        if ($termo === null) {
            return [];
        }
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        $atributos = [
            'usuarios.id',
            'usuarios.nome',
        ];


        $responsaveis = $this->select($atributos)
                            ->join('grupos_usuarios', 'grupos_usuarios.usuario_id = usuarios.id')
                            ->join('grupos', 'grupos.id = grupos_usuarios.grupo_id')
                            ->like('usuarios.nome', $termo)
                            ->where('usuarios.ativo', true)
                            ->where('grupos.exibir', true)
                            ->where('grupos.id !=', 2) // garanto que não exibiremos usuários clientes
                            ->where('grupos.deletado_em', null)
                            ->where('usuarios.deletado_em', null)
                            ->groupBy('usuarios.nome')
                            ->findAll();

        if ($responsaveis === null) {
            return [];
        }


        return $responsaveis;
    }


    public function recuperaAtendentesParaRelatorio(string $dataInicial, string $dataFinal)
    {
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'COUNT(ordens_responsaveis.usuario_abertura_id) AS quantidade_ordens'
        ];


        $where = 'ordens.criado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
                    ->join('ordens_responsaveis', 'ordens_responsaveis.usuario_abertura_id = usuarios.id')
                    ->join('ordens', 'ordens.id = ordens_responsaveis.ordem_id')
                    ->where($where)
                    ->withDeleted(true) // recupero também os usuários que já estão excluídos
                    ->groupBy('usuarios.nome')
                    ->orderBy('quantidade_ordens', 'DESC')
                    ->findAll();
    }


    public function recuperaResponsaveisParaRelatorio(string $dataInicial, string $dataFinal)
    {
        $this->db->simpleQuery("set session sql_mode=''"); // essa linha
 
        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'COUNT(ordens_responsaveis.usuario_responsavel_id) AS quantidade_ordens'
        ];


        $where = 'ordens.atualizado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
                    ->join('ordens_responsaveis', 'ordens_responsaveis.usuario_responsavel_id = usuarios.id')
                    ->join('ordens', 'ordens.id = ordens_responsaveis.ordem_id')
                    ->where('ordens.situacao !=', 'aberta')
                    ->where($where)
                    ->withDeleted(true) // recupero também os usuários que já estão excluídos
                    ->groupBy('ordens_responsaveis.usuario_responsavel_id')
                    ->orderBy('quantidade_ordens', 'DESC')
                    ->findAll();
    }


	public function recuperaUsuarioParaLog(string $termo = null){

		if($termo === null){

			return [];
		}


		$clienteModel = new \App\Models\ClienteModel();


		$clientesUsuariosIDs = array_column($clienteModel->asArray()->select('usuario_id')->findAll(), 'usuario_id');


		$atributos = [
			'usuarios.id',
			'usuarios.nome',
			'usuarios.email',
		];


        if(empty($clientesUsuariosIDs)){

            return $this->asArray()->select($atributos)
						 ->withDeleted(true)
						 ->like('usuarios.nome', $termo)
						 ->findAll();


        }


		return $this->asArray()->select($atributos)
						 ->whereNotIn('usuarios.id', $clientesUsuariosIDs)
						 ->withDeleted(true)
						 ->like('usuarios.nome', $termo)
						 ->findAll();

	}


    public function recuperaAtendentesGrafico(string $anoEscolhido){

        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'COUNT(ordens_responsaveis.usuario_abertura_id) AS quantidade_ordens',
            'YEAR(ordens.criado_em) AS ano',
        ];


        return $this->select($atributos)
                    ->join('ordens_responsaveis', 'ordens_responsaveis.usuario_abertura_id = usuarios.id')
                    ->join('ordens', 'ordens.id = ordens_responsaveis.ordem_id')
                    ->where('YEAR(ordens.criado_em)', $anoEscolhido)
                    ->withDeleted(true)
                    ->groupBy('usuarios.nome')
                    ->orderBy('quantidade_ordens', 'DESC')
                    ->findAll();


    }

}
