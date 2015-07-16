<?php

namespace \IdnoPlugins\Importmoves {

    class Main extends \Idno\Common\Plugin {
        
        function connect($username = FALSE) {
            require_once 'external/PHPMoves.php';
            if (!empty(\Idno\Core\site()->config()->importmoves)) {
                $params = array(
                    'moves_client_id' => \Idno\Core\site()->config()->importmoves['moves_client_id'],
                    'moves_client_secret' => \Idno\Core\site()->config()->importmoves['moves_client_secret'],
                    'moves_redirect_uri' => \Idno\Core\site()->config()->importmoves['moves_callback_url']
            );                
            }
            
        }
    }

}
