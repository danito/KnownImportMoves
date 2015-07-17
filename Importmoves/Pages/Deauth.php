<?php

    /**
     * Plugin administration
     */

    namespace IdnoPlugins\Importmoves\Pages {

        /**
         * Default class to serve the homepage
         */
        class Deauth extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
                if ($importmoves = \Idno\Core\site()->plugins()->get('Importmoves')) {
                    if ($user = \Idno\Core\site()->session()->currentUser()) {
                        if ($remove = $this->getInput('remove')) {
                            if (is_array($user->importmoves)) {
                                if (array_key_exists($remove, $user->importmoves)) {
                                    unset($user->importmoves[$remove]);
                                }
                            } else {
                                $user->importmoves = false;
                            }
                        } else {
                            $user->importmoves = false;
                        }
                        $user->save();
                        \Idno\Core\site()->session()->refreshSessionUser($user);
                        if (!empty($user->link_callback)) {
                            error_log($user->link_callback);
                            $this->forward($user->link_callback); exit;
                        }
                    }
                }
                $this->forward($_SERVER['HTTP_REFERER']);
            }

            function postContent() {
                $this->getContent();
            }

        }

    }