<?php

/*
foreach(get_zims_involving($concept_id) as $spec) {
  $zim = new Zim($spec);
  pp($zim,'zee zim');
}
*/


//pp(ZimCollection::from_specs(get_zims_involving($concept_id)),'ZimColl?');
///$all = ZimCollection::all();
/////pp(count($all),'count of all');
///pp($first,'first?');

///$sub = $all->involving_concept_id($concept_id);
//pp(count($sub),'count of subset');
////pp($sub,'subset');

/** CONCEPT OBJECT TEST
$in_english = new Concept(1);
$orange_fruit = new Concept(18);
$color = new Concept(19);

$responses = $orange_fruit->concept_responses_to_concept($color);
//pp($responses,'responses to: orange fruit x color');

foreach($responses as $concept) {
  $text_responses = $concept->text_responses_to_concept($in_english);
  pp(array_shift($text_responses),'in english');
  //pp($concept->text_responses_to_concept($in_english),'text responses');
}
***/

//pp($concept,'concept');

//pp($concept->url(),'url');


//$fug_ids = get_zam_receivers($lang,'glyph url');
//$fug_id = get_first_zam_receiver($lang,'glyph url');
//$fug = new Concept($fug_id);


//$glyph_url_concept = new Concept(get_first_zam_receiver(1,'glyph url'));

///pp($glyph_url_concept,'glyph url concept');


////pp($concept->text_responses_to_concept($glyph_url_concept),'any glyphs?');


//$glyph_urls = $concept->text_responses_to_concept($glyph_url_concept);

////pp($glyph_urls,'urls');



