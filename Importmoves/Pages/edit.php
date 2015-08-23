<?php

namespace IdnoPlugins\Importmoves\Pages {

    class Edit extends \Idno\Common\Page {

        function getContent() {
            $this->createGatekeeper();    // This functionality is for logged-in users only
            
            $strDate = 'yesterday';
            $yesterday = date('Y-m-d', strtotime($strDate));
            // Are we loading an entity or does an entity with yesterday date already exists
            $moves_obj  = \IdnoPlugins\Importmoves\Moves::getOneFromAll(array('day' => $yesterday));
            if (!empty($moves_obj)){
                $object = $moves_obj;
            } else {
                $object = new \IdnoPlugins\Importmoves\Moves();
            }
            if (!empty($this->arguments)) {
                $object = \IdnoPlugins\Importmoves\Moves::getByID($this->arguments[0]);
            } else {
                $object = new \IdnoPlugins\Importmoves\Moves();
            }


            $t = \Idno\Core\site()->template();
            $body = $t->__(array('object' => $object))->draw('entity/Moves/edit');
            if (empty($object)) {
                $title = 'publish your Moves summary';
            } else {
                $title = 'Edit your Moves summary';
                print_r($this);
            }

            if (!empty($this->xhr)) {
                echo $body;
            } else {
                $t->__(array('body' => $body, 'title' => $title))->drawPage();
            }
        }

        function postContent() {
            $this->createGatekeeper();
            $new = false;
            if (!empty($this->arguments)) {
                $object = \IdnoPlugins\Importmoves\Moves::getByID($this->arguments[0]);
            }
            if (empty($object)) {
                $object = new \IdnoPlugins\Importmoves\Moves();
            }
            if ($object->saveDataFromInput($this)) {
                $forward = $this->getInput('forward-to', $object->getDisplayURL());
                $this->forward($forward);
            }
        }

    }

}