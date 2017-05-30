<?php 

get_header(); ?>

<button class="new-cct">New CCT</button>
<button class="new-ccc">New CCC</button>

<div class="concepts">
	

</div>

<?php
add_action('wp_footer',function(){
?>
<script type="text/javascript">



BSD.id = 0;

BSD.constants = {};
BSD.constants.TITLE = 1;


BSD.Asset = function(spec) {
	var self = BSD.PubSub({});
	self.spec = spec;
	return self;
};

BSD.Trio = function(spec) {
	var self = BSD.PubSub({});
	self.spec = spec;

	self.recv = spec[0];
	self.msg = spec[1];
	self.resp = spec[2];


	self.titlesOf = function(prop) {
		var candidates = BSD.cct.select(function(cct){
			return cct[prop] == self[prop] && cct.msg == BSD.constants.TITLE;
		});
		return candidates.map(function(o) { return o.resp; });
	};

	self.recvTitles = function() {
		return self.titlesOf('recv');
	};
	self.msgTitles = function() {
		return self.titlesOf('msg');
	};

	self.getResp = "not implemented";


	self.renderOn = function(wrap) {
		var inner = DOM.div().addClass('inner');
		inner.append(DOM.span(self.recvTitles()[0]));
		inner.append('&nbsp;');
		inner.append(DOM.span(self.msgTitles()[0]));
		inner.append('&nbsp;');
		inner.append(DOM.span(self.getResp()));
		wrap.append(inner);
	};
	return self;
};

BSD.CCT = function(spec) {
	var self = BSD.Trio(spec);

	self.getResp = function() {
		return self.resp;
	};

	return self;
};

BSD.CCC = function(spec) {
	var self = BSD.Trio(spec);
	self.respTitles = function() {
		return self.titlesOf('resp');
	};
	self.getResp = function() {
		return self.respTitles()[0];
	};

	return self;
};

var conceptsWrap = jQuery('.concepts');



BSD.assets = [];

BSD.cct = [];
BSD.ccc = [];

///base data...FIXME: don't use same container for specs.
BSD.id = 0;
BSD.cct.push([BSD.id +=1,BSD.constants.TITLE,'title']);
BSD.cct.push([BSD.id +=1,BSD.constants.TITLE,'image url']);
//bootstrap
BSD.cct = BSD.cct.map(function(o){	return BSD.CCT(o); });


BSD.remoteStorage.getItem('single',function(o) { 
	//console.log(o); 
	var combined = JSON.parse(o);
	BSD.ccc = combined.ccc;
	BSD.cct = combined.cct;

	//bootstrap
	BSD.cct = BSD.cct.map(function(o){	return BSD.CCT(o); });
	BSD.cct.forEach(function(cct){
		cct.renderOn(conceptsWrap);
	});

});




jQuery('.new-cct').click(function(){
	var name = prompt('concept name');
	BSD.id += 1;
	var assetSpec = { id: BSD.id, name: name };
	var asset = BSD.Asset(assetSpec);
	BSD.assets.push(asset);

	var cctSpec = [BSD.id,1,name];
	var cct = BSD.CCT(cctSpec);
	BSD.cct.push(cct);
	cct.renderOn(conceptsWrap);
});


jQuery('.new-ccc').click(function(){

	var cctSpec = [BSD.id,1,name];
	var cct = BSD.CCT(cctSpec);
	BSD.cct.push(cct);
	cct.renderOn(conceptsWrap);
});



campfire.subscribe('save-zz',function(){
	var ccc = BSD.ccc.map(function(o) { return o.spec; });
	var cct = BSD.cct.map(function(o) { return o.spec; });
	var combined = { 
		ccc: ccc,
		cct: cct
	};
	BSD.remoteStorage.setItem('single',JSON.stringify(combined));
});


</script>
<?php
});

get_footer(); ?>