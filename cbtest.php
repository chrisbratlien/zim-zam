<?php


pp(text_translations_of_concept(5,1),'translated with integers');

pp(text_translations_of_concept(new Concept(5),new Concept(1)),'translated with objects');


pp(get_zam_receivers(1,'colour'),'colour?');

///$apple = new Concept(5);

///pp($apple,'apple?');