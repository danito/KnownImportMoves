<?php

namespace IdnoPlugins\Importmoves\Pages {

  class Edit extends \Idno\Common\Page {
    function getContent() {
      $this->createGatekeeper();    // This functionality is for logged-in users only

      $day = date('Ymd', strtotime("yesterday"));
      if ($this->getInput('day')) {
        $day = $this->getInput('day');
      }
      $edit = false;

      if (!empty($this->arguments)) {
        // check if we are editing an existing entity
        $object = \IdnoPlugins\Importmoves\Moves::getByID($this->arguments[0]);
        $day = $object->day;
        $edit  = true;
      }
      // Are we loading an entity or does an entity with yesterday date already exists
      $moves_obj  = \IdnoPlugins\Importmoves\Moves::getOneFromAll(array('day' => $day));
      if (!empty($moves_obj) && $edit == false){
        $object = $moves_obj;
      }
      if (empty($object)){
          $object = new \IdnoPlugins\Importmoves\Moves();
      }
      $t = \Idno\Core\site()->template();
      $body = $t->__(array('object' => $object, 'movesday'=>$day))->draw('entity/Moves/edit');
      if (empty($object)) {
        $title = 'publish your Moves summary';
      } else {
        $title = 'Edit your Moves summary';
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
