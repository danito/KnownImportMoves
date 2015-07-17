<?php

namespace IdnoPlugins\Importmoves {

    class Main extends \Idno\Common\Plugin {
        function registerPages() {
             \Idno\Core\site()->addPageHandler('importmoves/callback', '\IdnoPlugins\Importmoves\Pages\Callback');
             \Idno\Core\site()->addPageHandler('admin/importmoves', '\IdnoPlugins\Importmoves\Pages\Admin');
             \Idno\Core\site()->addPageHandler('account/importmoves', '\IdnoPlugins\Importmoves\Pages\Account');
         /** Template extensions */
            // Add menu items to account & administration screens
            \Idno\Core\site()->template()->extendTemplate('admin/menu/items', 'admin/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('account/menu/items', 'account/importmoves/menu');
            
        }
        function getReqUrl() {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $request_url = $importmovesApi->requestURL();
            return $request_url;
        }
        function connect($username = FALSE) {
            require_once(dirname(__FILE__) . '/external/PHPMoves.php');
            if (!empty(\Idno\Core\site()->config()->importmoves)) {
                $params = array(
                    'moves_client_id' => \Idno\Core\site()->config()->importmoves['moves_client_id'],
                    'moves_client_secret' => \Idno\Core\site()->config()->importmoves['moves_client_secret'],
                    'moves_redirect_uri' => \Idno\Core\site()->config()->importmoves['moves_callback_url']
            ); 
                $client_id = \Idno\Core\site()->config()->importmoves['moves_client_id'];
                $client_secret = \Idno\Core\site()->config()->importmoves['moves_client_secret'];
                $redirect_url = \Idno\Core\site()->config()->importmoves['moves_callback_url'];
            } 
            return new \PHPMoves\Moves($client_id, $client_secret, $redirect_url);
            
        }
    }

}
