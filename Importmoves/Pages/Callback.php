<?php

/**
 * Plugin administration
 */

namespace IdnoPlugins\Importmoves\Pages {

    /**
     * Default class to serve the homepage
     */
    class Callback extends \Idno\Common\Page {

        function get() {
            $this->gateKeeper();
            if ($request_token = $this->getInput('code')) {
                if ($importmoves = \Idno\Core\site()->plugins()->get('importmoves')) {
                    $importmovesApi = $importmoves->connect();
                    $tokens = $importmovesApi->auth($request_token);
                    $access_token = $tokens['access_token'];
                    $profile = $importmovesApi->getProfile($access_token);
                    $importmovesApi->config['moves_user_token'] =  $access_token;
                    $importmovesApi->config['moves_user_refresh_token'] = $tokens['refresh_token'];
                    $user = \Idno\Core\site()->session()->currentUser();
                    $user->$importmoves = array(
                        user_token => $access_token,
                        user_refresh_token => $tokens['refresh_token'],
                        user_id => $profile['userId']
                            );
                    $user->save();
                    \Idno\Core\site()->session()->addMessage('Your Moves credentials were saved.');
                } else {
                    \Idno\Core\site()->session()->addMessage('Your Moves credentials could not be saved.');
                }
            }
        }

    }

}