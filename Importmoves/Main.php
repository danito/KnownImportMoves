<?php

namespace IdnoPlugins\Importmoves {

    class Main extends \Idno\Common\Plugin {

        function registerPages() {
            \Idno\Core\site()->addPageHandler('importmoves/auth', '\IdnoPlugins\Importmoves\Pages\Auth');
            \Idno\Core\site()->addPageHandler('importmoves/deauth', '\IdnoPlugins\Importmoves\Pages\DeAuth');
            \Idno\Core\site()->addPageHandler('importmoves/callback', '\IdnoPlugins\Importmoves\Pages\Callback');
            \Idno\Core\site()->addPageHandler('admin/importmoves', '\IdnoPlugins\Importmoves\Pages\Admin');
            \Idno\Core\site()->addPageHandler('account/importmoves', '\IdnoPlugins\Importmoves\Pages\Account');
            /** Template extensions */
            // Add menu items to account & administration screens
            \Idno\Core\site()->template()->extendTemplate('admin/menu/items', 'admin/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('account/menu/items', 'account/importmoves/menu');
            \Idno\Core\site()->template()->extendTemplate('onboarding/connect/networks', 'onboarding/connect/importmoves');
        }

        function getReqUrl() {
            $importmoves = $this;
            $importmovesApi = $importmoves->connect();
            $request_url = $importmovesApi->requestURL();
            return $request_url;
        }

        function connect($access_token = FALSE) {
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

                return new \PHPMoves\Moves($client_id, $client_secret, $redirect_url);
            }
            return false;
        }

        function getTokenValidation($access_token) {
            $importmoves = $this;
            $importmovesApi = $this->connect();
            return $importmovesApi->validate_token($access_token);
        }

        function refreshToken($refresh_token) {
            $importmoves = $this;
            $importmovesApi = $this->connect();
            $tokens = $importmovesApi->refresh($refresh_token);
            if (!empty($tokens)){
            $user = \Idno\Core\site()->session()->currentUser();            
            $user->$importmoves[$profile['userId']] = array(
                user_token => $tokens['access_token'],
                user_refresh_token => $tokens['refresh_token']
            );
            $user->save();
            return true;
            } else {
                return false;
            }
            
        }

        function getProfile($access_token) {
            $importmoves = $this;
            $importmovesApi = $this->connect();
            $profile = $importmovesApi->get_profile($access_token);
            return $profile;
        }
        
        function getRange($access_token, $endpoint = '/user/summary/daily', $start, $end) {
            $importmoves = $this;
            $importmovesApi = $this->connect();
            $summary = $importmovesApi->getRange($access_token,$endpoint,$start,$end);
            if ($summary){
                return $summary;
            } else return false;
        }

    }

}
