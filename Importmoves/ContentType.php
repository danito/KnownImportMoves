<?php

    namespace IdnoPlugins\Importmoves {

        class ContentType extends \Idno\Common\ContentType {

            public $title = 'Moves';
            public $category_title = 'Moves';
            public $entity_class = 'IdnoPlugins\\Importmoves\\Moves';
            public $type = "article";
            public $indieWebContentType = array('article','moves');

        }

    }