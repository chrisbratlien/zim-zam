<?php 

get_header(); ?>

<style>

	.thumb { 

		width: 30px; 

	}

	textarea { height: 400px; }


	.drop {
		background: #0f0;
		border: 1px solid #0a0;
		min-height: 10px;
		min-width: 10px;
	}

	.inner .drop {
		height: 100px;
		width: 100px;

	}

	.svg-wrap {
		width: 30px;
	}

	.inline { display: inline; }
	.inline-block { display: inline-block; }
	.grid-cell {

		border: 1px solid red;
		margin-left: 3px;
		min-width: 50px;
		padding: 1rem;
	}

</style>


<button class="btn btn-primary btn-sm btn-new-cct">New Concept</button>
<button class="btn btn-primary btn-sm btn-save">Save</button>

<!--
<div class="row">
	<div class="col-md-3 col-xs-3 new-recv drop">
	</div>
	<div class="col-md-3 col-xs-3 new-msg drop">
	</div>
	<div class="col-md-3 col-xs-3 new-resp drop">
		<input class="new-resp-text">
	</div>
	<div class="col-md-3 col-xs-3 new-save">
	<button class="btn btn-primary btn-sm btn-save-trio">Save Trio</button>
	</div>
</div>
-->
<!-- row -->

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



<div class="row trifecta">
</div>

<div class="row">
	<div class="col-md-12 col-xs-12 grid">
	</div>
</div>



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
///BSD.constants.SVG = 

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


BSD.Widgets.Discover = function(spec) {
	var textInput = DOM.input().attr('type','text');
	var drag = DOM.div().addClass('drag-target');
	var results = DOM.div().addClass('results');

	var self = BSD.PubSub({});
	self.renderOn = function(wrap) {


	};
	return self;
};


BSD.C = function(spec) {
	var self = BSD.PubSub({});

	self.spec = spec;

	var ccc = [];
	var cct = [];

	ccc = BSD.ccc.select(function(o){
		return o.recv == spec;
	});
	cct = BSD.cct.select(function(o){
		return o.recv == spec;
	});

	var inner = DOM.div().addClass('inner').attr('draggable',true); 


	inner.on('dragstart',function(evt){
		evt.originalEvent.dataTransfer.setData('text',spec);
	});


	var panel = DOM.div().addClass('link-panel');
	var messageDrop = DOM.div().addClass('drop msg-drop');
	var responseDrop = DOM.div().addClass('drop recv-drop');
	panel.append(messageDrop);
	panel.append(responseDrop);

	self.renderOn = function(wrap) {
		inner.append(DOM.div(self.name()));

		var imageURL = self.imageURL(); 
		var thumb = DOM.img().addClass('thumb').attr('src',imageURL || BSD.baseURL + '/images/zimzam-white.png');
		inner.append(thumb);

		var svgWrap = DOM.div().addClass('svg-wrap');
		self.svg(function(innerHTML){
			svgWrap.html(innerHTML);
			inner.append(svgWrap);
		});

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
			inner.append(imageInput);
		}

		var plus = DOM.i().addClass("fa fa-plus");
		plus.on('click',function(){
		});
		inner.append(plus);
		//inner.append(panel);

		wrap.append(inner);

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
	self.svg = function(success,error) {
		lookupC('svg',function(c){
			var hit = cct.detect(function(o){
				return o.msg == c.spec; //C's spec is just their id..
			});
			if (hit) { 
				success(hit.resp);
				return true; 
			}
			if (error) {
				error(false);
				return false;				
			}
		},error);
	};
	return self;
}


BSD.CandC = function(name) {
	var inner = DOM.div();
	var self = BSD.PubSub({});
	self.renderOn = function(wrap) {
		var ok = DOM.button('OK').addClass('btn btn-primary btn-sm');
		var cancel = DOM.button('x').addClass('btn btn-cancel btn-sm');
		var grp = DOM.div().addClass('btn-group');
		grp.append(ok);
		grp.append(cancel);
		inner.append(DOM.span(name));
		inner.append('&nbsp;');
		inner.append(grp);
		wrap.append(inner);

		ok.click(function(){
			self.publish('save');
		});
		cancel.click(function(){
			//wrap.remove(inner);
			self.destroy();	
		});

	}
	self.destroy = function() {
		inner.remove();
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
	BSD.ccc = BSD.ccc.map(function(o){	return BSD.CCC(o); });
	BSD.ccc.forEach(function(ccc){
		///cct.renderOn(conceptsWrap);
		greatest = Math.max(greatest,ccc.recv);
	});


	BSD.id = greatest;

	for (var i = 1; i <= BSD.id; i+=1) {
		var c = BSD.C(i);
		////c.renderOn(conceptsWrap);
		BSD.c.push(c);
	}

	campfire.publish('concepts-loaded');
});




jQuery('.btn-new-cct').click(function(){
	var name = prompt('concept name');
	if (!name) { return false; }
	campfire.publish('new-cct-name',name);
	//cct.renderOn(conceptsWrap);
});


campfire.subscribe('new-cct-name',function(name){
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
});


campfire.subscribe('new-cct',function(spec){
	var cct = BSD.CCT(spec);
	BSD.cct.push(cct);
});


campfire.subscribe('new-ccc',function(spec){
	var ccc = BSD.CCC(spec);
	BSD.ccc.push(ccc);
});


function lookupCCT(text,success,error) {
	var result = BSD.cct.detect(function(cct){ 
		return cct.resp.toLowerCase() == text.toLowerCase(); 
	});
	if (result) {
		success(result);
		return true;
	}
	if (error) {
		error(result);
		return false;		
	}
}

function lookupC(text,success,error) {
	lookupCCT(text,function(cct){
		var hit = BSD.c.detect(function(c){ 
			return c.spec == cct.recv;
		});
		if (hit) {
			success(hit);
			return false;
		}
		if (error) {
			error(text);
		}
	},error);
}


/***
jQuery('.new-ccc').click(function(){

	var cctSpec = [BSD.id,1,name];
	var cct = BSD.CCT(cctSpec);
	BSD.cct.push(cct);
	cct.renderOn(conceptsWrap);
});
***/



campfire.subscribe('save-zz',function(){
	var ccc = BSD.ccc.select(function(o) { return o; }).map(function(o) { return o.spec; });
	var cct = BSD.cct.select(function(o) { return o; }).map(function(o) { return o.spec; });
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

	lookupC(word,function(c){
		c.renderOn(conceptsWrap);
	},function(w){
		var name = word;
		var cand = BSD.CandC(word);
		cand.subscribe('save',function(){
			campfire.publish('new-cct-name',word);
			cand.destroy();
		});
		cand.renderOn(candidatesWrap);
	});

});

campfire.subscribe('save-from-notes',function(){
	//console.log("PONG");
	var content = myEditor.getContent();
	console.log(content,'content');
	var wrap = DOM.div(content);
	var text = wrap.text();

	text = text.replace(/\?|\"/g,'');

	lines = text.split(/\r|\n/);
	lines.forEach(function(line){
		if (already[line]) { return false; }
		already[line] = true;
		campfire.publish('maybe-conceptify',line);
	});

	text = text.replace(/\r|\n/g,' ');
	console.log('text is',text);
	var words = text.split(/\ +/);
	words.forEach(function(word) {
		if (already[word]) { return false; }
		already[word] = true;
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

/**
//Coinbase

x = [];
jQuery('.tx-amount').each(function(i,e) { var shares = jQuery(e).find('.positive').text(); var usd = jQuery(e).find('.transfer').text(); x.push([shares,usd]) })

jQuery('.tx-item').each(function(i,e) { var shares = jQuery(e).find('.positive').text().trim().split(/\ /)[0]; var usd = jQuery(e).find('.transfer').text().trim().split(/\ /)[0].replace(/\$/,''); var usdps = usd/shares; x.push([parseFloat(shares),usdps]); })
**/

BSD.importJSON('data/trades.json',function(data) { 
	console.log('trades',data); 
	BSD.trades = data; 
});

function getTrades(symbol,success,error) {
	var them = BSD.trades.select(function(trade){
		return trade[0] == symbol;
	});
	var snipped = them.map(function(o){
		return [o[1],o[2]]; //just #shares and USD pricepershare.
	});
	if (success) {
		success(snipped);
	}
	return snipped;
}

function breakEven(symbol,cb) {
	var trx = getTrades(symbol);
	var evolved = evolveTrx(trx);
	return evolved.toString();
}


/** TINYMCE **/
	tinymce.init({ 
		selector: '.notes',
		init_instance_callback: function (editor) {
			myEditor = editor;
    	editor.on('click', function (e) {
      		console.log('Element clicked:', e.target.nodeName);
    	});
			editor.on('keyup',function(e) {
				///console.log('keyup',e);
				waiter2s.beg(campfire,'save-from-notes');		
			});
  		}
	});

	BSD.Widgets.Trifecta = function(spec) {
		var self = BSD.PubSub({});


		var myEditor = false;
		var newSpec = [];
		self.newSpec = newSpec;

		self.renderOn = function(wrap) {
			var inner = wrap;
			var recvDrop = DOM.div().addClass('recv drop col-xs-1');
			var msgDrop = DOM.div().addClass('msg drop col-xs-1');
			var respDrop = DOM.div().addClass('resp drop col-xs-1');
			///
			var inner = DOM.div().addClass('inner col-xs-3');
			var respHTML = DOM.div();
			var respText = DOM.input().attr('type','text').attr('placeholder','text').attr('tabindex',2);


			[recvDrop,msgDrop,respDrop].forEach(function(div){
				div.on('dragover',function(evt){
					evt.preventDefault();
				});
			});
			recvDrop.on('drop',function(evt){
				var id = evt.originalEvent.dataTransfer.getData('text');
				var c = BSD.C(id);
				recvDrop.empty();
				c.renderOn(recvDrop);
				newSpec[0] = parseInt(id,10);
			});
			msgDrop.on('drop',function(evt){
					var id = evt.originalEvent.dataTransfer.getData('text');
					var c = BSD.C(id);
					msgDrop.empty();
					c.renderOn(msgDrop);
					newSpec[1] = parseInt(id,10);
			});
			respDrop.on('drop',function(evt){
					var id = evt.originalEvent.dataTransfer.getData('text');
					var c = BSD.C(id);
					respDrop.empty();
					c.renderOn(respDrop);
					newSpec[2] = parseInt(id,10);
			});

			
			respText.on('change',function(){
				newSpec[2] = this.value;
			});
			

			tinymce.init({ 
				target: respHTML[0],
				init_instance_callback: function (editor) {
					myEditor = editor;
		    	editor.on('click', function (e) {
		      		console.log('Element clicked:', e.target.nodeName);
		    	});
					editor.on('keyup',function(e) {
						///console.log('keyup',e);
						waiter2s.beg(self,'editor-update');		
					});
		  		}
			});

			var btnSave = DOM.button('save trio').addClass('btn btn-primary btn-sm');
			btnSave.click(function(){
				if (! (newSpec[0] && newSpec[1] && newSpec[2])) { 
					console.log('incomplete');
					return false;
				}
				if (typeof newSpec[2] == "string") {
					self.publish('new-cct',newSpec);
				}
				else {
					self.publish('new-ccc',newSpec);
				}
			});

			wrap.append(recvDrop);
			wrap.append(msgDrop);
			wrap.append(respDrop);
			inner.append(respText);
			inner.append(respHTML);
			wrap.append(inner);
			wrap.append(btnSave);

		};
		self.subscribe('editor-update',function(){
			newSpec[2] = myEditor.getContent();
			console.log('spec',newSpec);
		});

		return self;
	};


	BSD.Widgets.Grid = function() {
		var self = BSD.PubSub({});
		var perRow = 5;
			var table = DOM.table();
			var thead = DOM.thead();
			var tbody = DOM.tbody();
		/***
		self.refresh = function() {
			var rows = BSD.chunkify(BSD.c,perRow);
			rows.forEach(function(row){
			});
		};
		**/
		self.renderOn = function(wrap) {
			BSD.c.forEach(function(concept){
				var inline = DOM.div().addClass('inline-block grid-cell');
				inline.append(DOM.span(concept.spec));
				concept.renderOn(inline);
				wrap.append(inline);
			});
			//self.refresh();
		}
		return self;
	};




	var x = BSD.Widgets.Trifecta();
	x.renderOn(jQuery('.trifecta'));
	x.subscribe('new-ccc',function(o){ campfire.publish('new-ccc',o); });
	x.subscribe('new-cct',function(o){ campfire.publish('new-cct',o); });


campfire.subscribe('concepts-loaded',function(){
	var grid = BSD.Widgets.Grid({});
	grid.renderOn(jQuery('.grid'));
});



</script>
<?php
});

get_footer(); ?>