<?php 

get_header(); ?>

<style>

	.thumb { 

		width: 30px; 

	}
</style>


<button class="btn btn-new-cct">New Concept</button>
<button class="btn btn-save">Save</button>

<div class="concepts">
	

</div>

<?php
add_action('wp_footer',function(){
?>
<script type="text/javascript">



BSD.id = 0;

BSD.constants = {};
BSD.constants.TITLE = 1;
BSD.constants.IMAGEURL = 2;


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
	self.mesg = spec[1];
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

		/**
		var controls = DOM.div().addClass('controls');
		controls.append(DOM.span('+ C-C'));
		controls.append(DOM.span('+ C-Text'));
		wrap.append(controls);
		**/

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

BSD.C = function(spec) {
	var self = BSD.PubSub({});

	var ccc = [];
	var cct = [];

	ccc = BSD.ccc.select(function(o){
		return o.recv == spec;
	});
	cct = BSD.cct.select(function(o){
		return o.recv == spec;
	});


	self.renderOn = function(wrap) {
		wrap.append(DOM.div(self.name()));

		var imageURL = self.imageURL(); 
		var thumb = DOM.img().addClass('thumb').attr('src',imageURL || BSD.baseURL + '/images/zimzam-white.png');
		wrap.append(thumb);

		if (!imageURL) {
			var imageInput = DOM.input();
			imageInput.change(function(){
				console.log(this.value);
				var o = BSD.CCT([spec,BSD.constants.IMAGEURL,this.value]);
				BSD.cct.push(o);

				//FIXME: what about using fetch?
				jQuery.ajax({
					url: BSD.baseURL + '/ws',
					data: {
						action: 'get_glyph_from_url',
						url: this.value
					},
					success: function(o){
						console.log('o?',o);
						thumb.attr('src', BSD.baseURL + '/uploads/' + o);
						imageInput.remove();///replaceWith(thumb);
					}
				});
			});
			wrap.append(imageInput);
		}
	};

	self.name = function() {
		var hit = cct.detect(function(o){
			return o.msg == BSD.constants.TITLE;
		});
		return hit.resp;
	};
	self.imageURL = function() {
		var hit = cct.detect(function(o){
			return o.msg == BSD.constants.IMAGEURL;
		});
		if (!hit) { return false; }
		return hit.resp;
	};


	return self;
}



BSD.assets = [];

BSD.cct = [];
BSD.ccc = [];
BSD.c = [];

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
	var greatest = BSD.id;


	BSD.cct = BSD.cct.map(function(o){	return BSD.CCT(o); });
	BSD.cct.forEach(function(cct){
		///cct.renderOn(conceptsWrap);
		greatest = Math.max(greatest,cct.recv);
	});
	BSD.id = greatest;

	for (var i = 1; i <= BSD.id; i+=1) {
		var c = BSD.C(i);
		c.renderOn(conceptsWrap);
		BSD.c.push(c);
	}

});




jQuery('.btn-new-cct').click(function(){
	var name = prompt('concept name');
	if (!name) { return false; }
	BSD.id += 1;
	var assetSpec = { id: BSD.id, name: name };
	var asset = BSD.Asset(assetSpec);
	BSD.assets.push(asset);

	var cctSpec = [BSD.id,1,name];
	var cct = BSD.CCT(cctSpec);
	BSD.cct.push(cct);
	var c = BSD.C(BSD.id);
	c.renderOn(conceptsWrap);
	BSD.c.push(c);
	//cct.renderOn(conceptsWrap);
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

jQuery('.btn-save').click(function(){
	campfire.publish('save-zz');
});


</script>
<?php
});

get_footer(); ?>