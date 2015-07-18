<?php

    /**
     * Plugin administration
     */

    namespace IdnoPlugins\Importmoves\Pages {

        /**
         * Default class to serve the homepage
         */
        class Admin extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->adminGatekeeper(); // Admins only
                $t = \Idno\Core\site()->template();
                $body = $t->draw('admin/importmoves');
                $t->__(array('title' => 'Import Moves', 'body' => $body))->drawPage();
            }

            function postContent() {
                $this->adminGatekeeper(); // Admins only
                $moves_redirect_url = trim($this->getInput('moves_redirect_url'));
           echo     $moves_client_id = trim($this->getInput('moves_client_id'));
                $moves_client_secret = trim($this->getInput('moves_client_secret'));
                \Idno\Core\site()->config->config['importmoves'] = array(
                    'moves_client_id' => $moves_client_id,
                    'moves_client_secret' => $moves_client_secret,
                    'moves_redirect_url' => $moves_redirect_url
                );
               
                \Idno\Core\site()->config()->save();
                \Idno\Core\site()->session()->addMessage('Your Moves application details were saved.');
                $this->forward(\Idno\Core\site()->config()->getDisplayURL() . 'admin/importmoves/');
            }

        }

    }