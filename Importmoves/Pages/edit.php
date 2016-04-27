<?php

namespace IdnoPlugins\Importmoves\Pages {

  class Edit extends \Idno\Common\Page {
    function getContent() {
      $this->createGatekeeper();    // This functionality is for logged-in users only

      $day = date('Ymd', strtotime("yesterday"));
      if ($this->getInput('day')) {
        $day = $this->getInput('day');
        error_log("EDIT DAY ".$day);
      }
      $edit = false;
      $object = new \IdnoPlugins\Importmoves\Moves();
      if (!empty($this->arguments)) {
        // check if we are editing an existing entity
        $object = \IdnoPlugins\Importmoves\Moves::getByID($this->arguments[0]);
        $day = $object->day;
        $edit  = true;
        error_log("EDIT EXISTING ". print_r($this->arguments,true));
        error_log("OBJECT ". print_r($object,true));
        error_log("DAY ". print_r($day,true));
      }
      // Are we loading an entity or does an entity with yesterday date already exists
      $moves_obj  = \IdnoPlugins\Importmoves\Moves::getOneFromAll(array('day' => $day));
      if (!empty($moves_obj) && $edit == FALSE){
        $object = $moves_obj;
        error_log("not empty yesterday");
      }
error_log("OBJECT 2 ". print_r($object,true));

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
