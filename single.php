<?php 

get_header(); ?>

<button class="new-concept">New</button>

<div class="concepts">
	

</div>

<?php
add_action('wp_footer',function(){
?>
<script type="text/javascript">


BSD.assets = [];



jQuery('.new-concept').click(function(){
	var name = prompt('concept name');
	var asset = { name: name };
	BSD.assets.push(asset);
});


</script>
<?php
});

get_footer(); ?>