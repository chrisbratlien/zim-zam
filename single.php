<?php 

get_header(); ?>

<style>

	.thumb { 

		width: 30px; 

	}

	textarea { height: 400px; }
</style>


<button class="btn btn-new-cct">New Concept</button>
<button class="btn btn-save">Save</button>
<div class="row">
	<div class="col-md-3 col-xs-3">
		<div class="concepts">
		</div>
	</div>
	<div class="col-md-3 col-xs-3">
		<div class="candidates">
		</div>
	</div>
	<div class="col-md-6 col-xs-6">
		<textarea class="notes"></textarea>
	</div>
</div><!-- row -->

<?php
add_action('wp_footer',function(){
?>

<!--<script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey=mukkqxrd91gce8y7kazftgi8aer53cv6hqf0wdisgyhc9dp1"></script>-->
<script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
<script type="text/javascript">

var myEditor = false; 
var waiter = BSD.Widgets.Procrastinator({ timeout: 400 });
var waiter2s = BSD.Widgets.Procrastinator({ timeout: 2000 });
  

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
var candidatesWrap = jQuery('.candidates');

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


BSD.CandC = function(name) {
	var self = BSD.PubSub({});
	self.renderOn = function(wrap) {
		var inner = DOM.div();
		var ok = DOM.button('OK').addClass('btn btn-primary');
		var cancel = DOM.button('x').addClass('btn btn-cancel');
		var grp = DOM.div().addClass('btn-group');
		grp.append(ok);
		grp.append(cancel);
		inner.append(DOM.span(name));
		inner.append(grp);
		wrap.append(inner);

		ok.click(function(){
			self.publish('save');
		});
		cancel.click(function(){
			//wrap.remove(inner);
			inner.remove();
		});

	}
	return self;
};


BSD.rat = function(spec,y) {
	var self = {};

	if (y) {
		spec = { x: spec, y: y }
	}
	self.spec = spec;



	self.area = function() {
		return spec.x * spec.y;
	};


	self.evolve = function(other) {
		var result = BSD.rat({
			x: spec.x + other.spec.x,
			y: (self.area() + other.area()) / (spec.x + other.spec.x)
		});
		return result;
	};

	self.fifo = function(x) {

	}



	self.toString = function() {
		return spec.x + ' @ ' + spec.y;
	}


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


var already = {};

campfire.subscribe('maybe-conceptify',function(word){
	var name = word;
	if (already[name]) { return false; }
	already[name] = true;

	var cand = BSD.CandC(word);
	cand.renderOn(candidatesWrap);
	/***
	BSD.id += 1;
	var assetSpec = { id: BSD.id, name: name };
	var asset = BSD.Asset(assetSpec);
	BSD.assets.push(asset);

	var cctSpec = [BSD.id,1,name];
	var cct = BSD.CCT(cctSpec);
	BSD.cct.push(cct);
	var c = BSD.C(BSD.id);
	c.renderOn(candidatesWrap);
	BSD.c.push(c);
	***/
});

campfire.subscribe('save-from-editor',function(){
	//console.log("PONG");
	var content = myEditor.getContent();

	var wrap = DOM.div(content);
	var text = wrap.text();

	text = text.replace(/\?|\"/g,'');
	text = text.replace(/\r|\n/,' ');
	console.log('text is',text);
	var words = text.split(/\ +/);
	words.forEach(function(word) {
		campfire.publish('maybe-conceptify',word);
	});
});



////accum = [[2,270],[1,290],[1,305],[2,315],[1,310]].inject(BSD.rat(0,1),function(accum,o) { return accum.evolve(BSD.rat(o[0],o[1])); })

function evolveTrx(them) {
	var result = them.inject(BSD.rat(0,1),function(accum,o) { return accum.evolve(BSD.rat(o[0],o[1])); });
	return result;
}

function only(them,x) {
	var accum = 0;
	var result = them.map(function(pair){
		var px = pair[0];
		accum += px;
		var diff = accum - x;
		if (diff > 0) {
			accum = x;
			return [px-diff,pair[1]];
		}
		return [px,pair[1]];
	});
	return result;
}

function fifo(them,x) {
	return evolveTrx(only(them,x));
	///var justx = them.map(function())
}




//Coinbase
/**

x = [];
jQuery('.tx-amount').each(function(i,e) { var shares = jQuery(e).find('.positive').text(); var usd = jQuery(e).find('.transfer').text(); x.push([shares,usd]) })

jQuery('.tx-item').each(function(i,e) { var shares = jQuery(e).find('.positive').text().trim().split(/\ /)[0]; var usd = jQuery(e).find('.transfer').text().trim().split(/\ /)[0].replace(/\$/,''); var usdps = usd/shares; x.push([parseFloat(shares),usdps]); })

BTC =
[
    [
        0.00431868,
        1204.0716144747933
    ],
    [
        0.00401531,
        1295.0432220675364
    ],
    [
        0.03571518,
        1164.7708341383131
    ],
    [
        0.12111015,
        1257.0374985085891
    ],
    [
        0.04209536,
        1205.3585003192752
    ],
    [
        0.04757334,
        1066.5637518828823
    ],
    [
        0.08358533,
        1244.1178374243423
    ],
    [
        0.07940607,
        1244.1114388358474
    ]
]"



"0.41781942 @ 1217.0329469128073"


ETH =

[
    [
        0.30447518,
        17.078567783423267
    ],
    [
        2.08564508,
        18.94857393473678
    ],
    [
        1.10215427,
        46.03711239080896
    ],
    [
        1.10226454,
        46.032506860830345
    ],
    [
        2.19898107,
        47.290084220688634
    ],
    [
        1.85412151,
        164.21253858383858
    ]
]
"8.64764165 @ 64.14003059435287"
**/

	tinymce.init({ 
		selector:'textarea',
		init_instance_callback: function (editor) {
			myEditor = editor;
    	editor.on('click', function (e) {
      	console.log('Element clicked:', e.target.nodeName);
    	});
			editor.on('keyup',function(e) {
				///console.log('keyup',e);
				waiter2s.beg(campfire,'save-from-editor');		
			});
  	}
	});


</script>
<?php
});

get_footer(); ?>