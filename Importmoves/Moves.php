<?php

namespace IdnoPlugins\Importmoves {

    class Moves extends \Idno\Common\Entity {

        function getTitle() {
            if (empty($this->title)) {
                return 'Untitled';
            } else {
                return $this->title;
            }
        }

        function getURL()
            {
                // If we have a URL override, use it
                if (!empty($this->url)) {
                    return $this->url;
                }
                if (!empty($this->canonical)) {
                    return $this->canonical;
                }
                if (!$this->getSlug() && ($this->getID())) {
                    return \Idno\Core\site()->config()->url . 'moves/' . $this->getID() . '/' . $this->getPrettyURLTitle();
                } else {
                    return parent::getURL();
                }
            }

        function getDescription() {
            return $this->body;
        }

        function getData() {
            return $this->data;
        }

        function getDay() {
            return $this->day;
        }

        /**
         * will be probably obsolete as all entities should be articles
         * @return 'article'
         */
        function getActivityStreamsObjectType() {
            return 'article';
        }

        /**
         * @return bool
         */
        function saveDataFromInput() {

            if (empty($this->_id)) {
                $new = true;
            } else {
                $new = false;
            }
            $this->title = \Idno\Core\site()->currentPage()->getInput('title');
            $this->body = \Idno\Core\site()->currentPage()->getInput('body');
            $this->tags = \Idno\Core\site()->currentPage()->getInput('tags');
            $this->data = \Idno\Core\site()->currentPage()->getInput('data');
            $this->day = \Idno\Core\site()->currentPage()->getInput('day');

            $this->setAccess('PUBLIC');
            if ($time = \Idno\Core\site()->currentPage()->getInput('created')) {
                $this->created = $time;
            }
            if ($this->save()) {

                return true;
            } else {
                return false;
            }

        }

    }

}
