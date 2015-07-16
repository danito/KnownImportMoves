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
            if ($code = $this->getInput('code')) {
                if ($importmoves = \Idno\Core\site()->plugins()->get('KnownImportMoves')) {
                    
                }
            }
        }

    }

}