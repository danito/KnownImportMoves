<?php

    /**
     * Plugin administration
     */

    namespace IdnoPlugins\Importmoves\Pages {

        /**
         * Default class to serve the homepage
         */
        class Account extends \Idno\Common\Page
        {
            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
                /*if ($twitter = \Idno\Core\site()->plugins()->get('Twitter')) {
                    $oauth_url = $twitter->getAuthURL();
                }*/
                $oauth_url = \Idno\Core\site()->config()->getDisplayURL() . 'importmoves/auth';
                $summary = "This should be your daily summary";
                $t = \Idno\Core\site()->template();
                $validation = "";
                $body = $t->__(array('oauth_url' => $oauth_url, 'summary' => $summary))->draw('account/importmoves');
                $t->__(array('title' => 'Import Moves', 'body' => $body))->drawPage();
            }

            function postContent() {
                $this->gatekeeper(); // Logged-in users only
                if (($this->getInput('remove'))) {
                    $user = \Idno\Core\site()->session()->currentUser();
                    $user->importmoves = array();
                    $user->save();
                    \Idno\Core\site()->session()->addMessage('Your Moves settings have been removed from your account.');
                }
                $this->forward(\Idno\Core\site()->config()->getDisplayURL() . 'account/importmoves/');
            }

        }

    }
