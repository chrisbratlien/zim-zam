<?php

echo '123';

$glyph_url_id = get_first_zam_receiver(1,'glyph url');


pp($glyph_url_id,'id');
exit;
exit;


$them = get_zams_where(Array(sprintf('message = %d',$glyph_url_id)));

pp($them,'them');