<?php

class ZimCollection {

  var $array;
  function __construct($a) {
  
    //pp('i got constructed');
  
    $this->array = $a;
  }

  //class functions
  static function from_specs($them) {
    $result = array_map(function($elem) {
        return new Zim($elem);
      },$them);
        
    //pp($result,'resulllllllllt');


    return new ZimCollection($result);
  }
  
  
  static function all() {
    $specs = get_all_zims();
    /////pp($specs,'specs?');
    return ZimCollection::from_specs($specs);
  }
  

  //instance functions
  function involving_concept_id($concept_id) {
    //todo: verify z is a zim.
  
    $result = array_filter($this->array,function($elem) use($concept_id) {
      return $elem->involves_concept_id($concept_id);
    });
    return new ZimCollection($result);
  }
  
  
  function first() {
    if (empty($this->array)) { return false; }
    ////if (count($this->array) < 1) { return false; }
    return $this->array[0];
  }
  
}


class Zim {

  var $spec;

  function __construct($spec) {
    $this->spec = $spec;
  }
  
  function involves_concept_id($id) {
  
    //pp($this,'this');
    ///pp($id,'id');
  
    return (
      $this->spec->receiver == $id || 
      $this->spec->message == $id || 
      $this->spec->response == $id
    );
  }  
  
  
  
  
  
  function foo() {
  
  
  }

}