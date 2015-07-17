<?php

/**
 * Plugin administration
 */

namespace IdnoPlugins\Importmoves\Pages {

    /**
     * Default class to serve the homepage
     */
    class Auth extends \Idno\Common\Page {

        function getContent() {
            $this->gatekeeper(); // Logged-in users only
            if ($importmoves = \Idno\Core\site()->plugins()->get('Importmoves')) {
                $login_url = $importmoves->getReqUrl();
                if (!empty($login_url)) {
                    $this->forward($login_url);
                    exit;
                }
            }
            $this->forward($_SERVER['HTTP_REFERER']);
        }

        function postContent() {
            $this->getContent();
        }

    }

}