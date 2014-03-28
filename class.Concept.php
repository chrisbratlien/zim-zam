<?php

class ConceptCollection {

  var $array;
  function __construct($a) {
    $this->array = $a;
  }

  //class functions
  static function from_ids($them) {
    $result = array_map(function($id) {
        return new Concept($id);
      },$them);
    return new ConceptCollection($result);
  }
  
  static function all() {
    $specs = get_all_concepts();
    /////pp($specs,'specs?');
    return ConceptCollection::from_ids($ids);
  }



/*
  //instance functions
  function involving_concept_id($concept_id) {
  
    $result = array_filter($this->array,function($elem) use($concept_id) {
      return $elem->involves_concept_id($concept_id);
    });
    return new ConceptCollection($result);
  }
*/ 

  function first() {
    if (empty($this->array)) { return false; }
    ////if (count($this->array) < 1) { return false; }
    return $this->array[0];
  }
}

class Concept {

  ///var $spec;
  var $id;

  function __construct($id) {
    $this->id = $id;
  }
  
  function url() {
    return sprintf('%s/concept/%d',get_bloginfo('url'),$this->id);
  }
  
  function concept_responses_to_concept($other) {
    //other must be a concept or an integer concept id
    //find zims where $other's id is the message, $this's id is the receiver
    //return a ConceptCollection of all the repsonse concepts
    $set = get_zims_where(Array(
      sprintf('(receiver = %d)',$this->id),
      sprintf('(message = %d)',$other->id)
    ),'AND');
    ///pp($set,'set');
    
    /****
    // style A    
    $ids = Array();
    foreach($set as $zim_spec) {
      array_push($ids,$zim_spec->response);
    }
    /////pp($ids,'ids');
    return ConceptCollection::from_ids($ids);
    ***/
    /***
    //style B
    $concepts = Array();
    foreach($set as $zim_spec) {
      array_push($concepts,new Concept($zim_spec->response));
    }
    return new ConceptCollection($concepts);
    ***/
    

    //style C
    $concepts = Array();
    foreach($set as $zim_spec) {
      array_push($concepts,new Concept($zim_spec->response));
    }
    return $concepts;  
  }

  function text_responses_to_concept($other) {
    //other must be a concept or an integer concept id
    //find zams where $other's id is the message, $this's id is the receiver
    //return all textual responses in an array.  

    $set = get_zams_where(Array(
      sprintf('(receiver = %d)',$this->id),
      sprintf('(message = %d)',$other->id)
    ),'AND');
    //pp($set,'set');
    //return 'blah';
    
    $strings = Array();
    foreach($set as $zam_spec) {
      array_push($strings,$zam_spec->spec->response);
    }
    return $strings;
  }
       
}