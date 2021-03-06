<?php

/**
 * Plugin administration
 */

namespace IdnoPlugins\Importmoves\Pages {

    /**
     * Default class to serve the homepage
     */
    class Callback extends \Idno\Common\Page {

        function getContent() {
            $this->gateKeeper();
            
            if ($request_token = $this->getInput('code')) {
                error_log("Code: ". $this->getInput('code'));
            
                if ($importmoves = \Idno\Core\site()->plugins()->get('Importmoves')) {
                    error_log("Importmoves ok");
                    $importmovesApi = $importmoves->connect();
                    $tokens = $importmovesApi->auth($request_token);
                    $access_token = $tokens['access_token'];
                    $profile = $importmovesApi->get_profile($access_token);
                    $importmoves->config['moves_user_token'] =  $access_token;
                    $importmoves->config['moves_user_refresh_token'] = $tokens['refresh_token'];
                    $user = \Idno\Core\site()->session()->currentUser();
                    $firstactivity = $user->created;
                            
                    $user->importmoves = array(
                        'user_token' => $access_token,
                        'user_refresh_token' => $tokens['refresh_token'],
                        "user_id" => $profile['userId'],
                        "user_first_date" => $profile['profile']['firstDate'],
                        "user_last_import" => $firstactivity
                            );
                    $user->save();
                    \Idno\Core\site()->session()->addMessage('Your Moves credentials were saved.');
                } else {
                    \Idno\Core\site()->session()->addMessage('Your Moves credentials could not be saved.');
                }
                $this->forward('/account/importmoves/');
            }
        }

    }

}