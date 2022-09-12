<?php

    if(function_exists('usuario_logado') === false){
        function usuario_logado(){
            return service('autenticacao')->pegaUsuarioLogado();
        }
    }