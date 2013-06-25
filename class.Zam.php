<?php

class Zam {

  var $spec;

  function __construct($spec) {
    $this->spec = $spec;
  }
  
  function involves_concept_id($id) {
    return ($this->spec->receiver == $id || $this->spec->message == $id);
  }  
}