<?php



init_db();

/***
q('?',1,'fruit');

// ==>


q('?',ZZ_IS_A,ZZ_USER);

=>[53,21,345,112,55] #array of concept ids. 

# but what if we query multiple wildcards ? we're not always solving for one thing…

///so this is just a zim queurier?
// a zam querier?

// a both querier?
***/

//$set = askzim(5,4,6);
///$set = askzim(WILD,WILD,WILD);
///$set = askzim(WILD,4,6);
//$set = askzim('five',4,6);


//pp(get_zims_with_response(6),'resp=6');
//pp(get_zims_with_message(4),'mesg=4');
//pp(get_zims_with_receiver(5),'recv=5');


//pp(get_all_zims(),'ALL');
/////exit;

$english = array_shift(askzam(WILD,WILD,'in english'))->spec->receiver;
///pp($english,'english');
$set = askzimzam(WILD,$english,'fruit');
pp($set,'set');

/**
pp(text_translations_of_concept(5,1),'translated with integers');
pp(text_translations_of_concept(new Concept(5),new Concept(1)),'translated with objects');
pp(get_zam_receivers(1,'colour'),'colour?');
****/

///$apple = new Concept(5);

///pp($apple,'apple?');