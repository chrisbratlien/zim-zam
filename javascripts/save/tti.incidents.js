if (typeof TTI == "undefined") {
  var TTI = {};
}
if (typeof TTI.Widgets == "undefined") {
  TTI.Widgets = {};
}


TTI.Validator = function() {
  var required = [];
  var interface = {};

  interface.registerAsRequired = function(jqObject) {
    required.push(jqObject);
  };
  interface.validateForm = function() {
    var blank = required.select(function(field) {
      return (field.val() === '');
    });
    return (blank.length === 0);
  }; 
  return interface;
};

TTI.points = []; //holds all the points known

TTI.Point = function(spec) { //constructor

  var interface = {};
  interface.x = parseFloat(spec.x);
  interface.y = parseFloat(spec.y);
  interface.mile = spec.mile;  
  
  
  interface.distanceTo = function(otherPoint) {
    var a = interface.x - otherPoint.x;
    var b = interface.y - otherPoint.y;
    
    var c = Math.sqrt(a*a + b*b); //Pythagorean, solve for c
    return c;     
  };

  return interface;
};

TTI.pointClosestTo = function(otherPoint) {
  var candidates = TTI.points.collect(function(p) { return {point: p, distance: p.distanceTo(otherPoint), mile:  p.mile }; });
  ///console.log(candidates,'candidates');
  var sorted = candidates.sort(function(a,b) {
    return a.distance - b.distance;
  });
  ////console.log(sorted,'sorted');
  var result = sorted[0];
  
  return result.point;
  ////return result;
};


TTI.Incident = function(spec) {

  ///console.log('spec',spec);

  var interface = spec;
  
  interface.onUpdate = spec.onUpdate || function(html) {};  
  interface.onSave = spec.onSave || function(html) {};  
  
  interface.whenDate = function() {
    return new Date(interface.when);
  };
  

  interface.prettyDate = function() {
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var when = interface.whenDate();  
    return '{month} {d}, {h}:{m}'.supplant({
      month: months[when.getMonth()],
      d: when.getDate(),
      h: when.getHours(),
      m: ('0000' + when.getMinutes()).substr(-2)
    });
  };

  interface.update = function() {
    var payload = {
      action: 'update_incident',
      incident_id: interface.incident_id,
      status: interface.status,
      description: interface.description
    };
    
    jQuery.ajax({
      type: 'POST',
      url: TTI.baseURL + '/ws',
      data: payload,
      success: function(html) {
        interface.onUpdate(html);       
      }
    });        
  };   


  interface.save = function() {
    console.log('save!!');
    var payload = {
      action: 'report_incident',
      roadway: interface.roadway,
      mile_marker_adjust: interface.mile_marker_adjust,
      mile_marker: interface.mile_marker,
      direction: interface.direction,
      vehicle_count: interface.vehicle_count,
      status: interface.status,
      description: interface.description,
      cross_street: interface.cross_street,
      latitude: interface.latitude,
      longitude: interface.longitude,
      affected_unknown: interface.affected_unknown,
      affected_left_shoulder: interface.affected_left_shoulder,
      affected_lane1: interface.affected_lane1,
      affected_lane2: interface.affected_lane2,
      affected_lane3: interface.affected_lane3,
      affected_lane4: interface.affected_lane4,
      affected_lane5: interface.affected_lane5,
      affected_lane6: interface.affected_lane6,
      affected_lane7: interface.affected_lane7,
      affected_lane8: interface.affected_lane8,
      affected_right_shoulder: interface.affected_right_shoulder,
      affected_entrance_ramp: interface.affected_entrance_ramp,
      affected_exit_ramp: interface.affected_exit_ramp,
      affected_connector: interface.affected_connector,
      affected_hov_lane: interface.affected_hov_lane,
      severity: interface.severity
    };
    
    console.log('payload',payload);
    
    jQuery.ajax({
      type: 'POST',
      url: TTI.baseURL + '/ws',
      data: payload,
      success: function(html) {
        interface.onSave(html);
      }
    });    
  };   
  return interface;
};

TTI.getRecentIncidents = function() {
  var data = jQuery.ajax({ 
    url: TTI.baseURL + '/ws', 
    type: 'POST',
    data: { action: 'recent_or_uncleared_incidents' }, 
    async: false
    }).responseText;

  var them = eval(data);
  
  var incidents = them.map(function(i){
    return TTI.Incident(i);
  });
  
  return incidents;
};





TTI.Widgets.MainPresentation = function(spec) {


  var updateForm = false;
  var newForm = false;
  
  var secondaryDiv = jQuery('#secondary');

  var interface = {};

  interface.go = function() {
    var menu = TTI.Widgets.IncidentsMenu({
      presentation: interface
    });
    menu.renderOn(jQuery('#incidents-picker'));
  };  

  interface.newIncident = function() {
    form = TTI.Widgets.NewIncidentForm({
      //////"incident": incident,
      onClose: function() {
        secondaryDiv.empty();
        secondaryDiv.hide();
      }
    });
    secondaryDiv.empty();
    form.renderOn(secondaryDiv);
    secondaryDiv.show();
  };


  
  interface.updateIncident = function(incident) {
    updateForm = TTI.Widgets.UpdateIncidentForm({
      "incident": incident,
      onClose: function() {
        secondaryDiv.empty();
        secondaryDiv.hide();
      }
    });
    secondaryDiv.empty();
    updateForm.renderOn(secondaryDiv);
    secondaryDiv.show();
  };

  interface.clearIncident = function(incident) {
    incident.oldStatus = incident.status; //in case user cancels the form, we'll restore this value
    incident.status = 'Cleared';
    interface.updateIncident(incident);
  };

  return interface;
};



TTI.Widgets.IncidentsMenu = function(spec) {

  var interface = {};


  interface.renderMileMarkerOn = function(html,mile) {
    var result = jQuery('<div class="mile-marker shadow">MILE {mile}</div>'.supplant({ "mile": mile }));
    html.append(result);
  };
  
  interface.renderOn = function(html) {

    var newIncidentButton = jQuery('<button id="new" class="yellow" type="submit">New Incident</button>');
    newIncidentButton.click(function() {
      ////document.location.href = TTI.baseURL + '/new-incident';
      spec.presentation.newIncident();  
    });
    
    
    html.append(newIncidentButton);


    var table = jQuery('<table>');
      var headerRow = jQuery('<tr><th colspan="3">Recent Incidents</th></tr>');
    table.append(headerRow);
    
    TTI.getRecentIncidents().each(function(incident) {
      var row = jQuery('<tr>');
      
        var updateCell = jQuery('<td class="update-incident"></td>');
          var updateButton = jQuery('<button class="yellow table-button">Update</button>');
          updateButton.click(function() {
            ////console.log('href',incident.updateURL);
            ////document.location.href = incident.updateURL;
            incident.oldStatus = incident.status; //save off last value.
            spec.presentation.updateIncident(incident);
            
          });
        updateCell.append(updateButton);

        


        var infoCell = jQuery('<td class="info incident"></td>');
        var desc = '#{id} | {when} | MM{mile_marker} | {roadway} | {direction} | {count} vehicle | Status: {status} | {description}'.supplant({
          id: incident.incident_id,
          when: incident.prettyDate(),
          mile_marker: incident.mile_marker,
          roadway: incident.roadway,
          direction: incident.direction,
          count: incident.vehicle_count,
          status: incident.status,
          description: incident.description
        });
        
        interface.renderMileMarkerOn(infoCell,incident.mile_marker);

        infoCell.append(desc);
        
        
        //console.log('incident',incident);
        
        var clearCell = jQuery('<td class="clear-incident"></td>');
          var clearButton = jQuery('<button class="yellow table-button">Clear</button>');
          clearButton.click(function() {
            spec.presentation.clearIncident(incident);
          });
        clearCell.append(clearButton);          
        
        
        row.append(updateCell);
        row.append(infoCell);
        row.append(clearCell);
      table.append(row);
    });
  
    html.append(table);
  };
  return interface;
};


TTI.Widgets.Checkbox = function(spec) {

  var interface = {};
  
  interface.renderOn = function(html) {
    var wrap = jQuery('<div class="tti-checkbox"></div>');
    var label = jQuery('<label>');
    var cb = jQuery('<input type="checkbox" />');
    
    //FIXME: need to decide as a rule if spec.id should go on the wrapper div or on the actually active element....
    //but in this case, the label for= needs to match the checkbox id, so that's why I'm doing that here.
    if (spec.id) {
      cb.attr('id',spec.id);
      label.attr('for',spec.id);
      ///label.click(function() {}); //work around iOS bug where labels aren't clickable.
    }

    cb.click(spec.onChange);
    label.append(cb);
    label.append(spec.label);

    ///wrap.append(cb);
    wrap.append(label);
    html.append(wrap);
  }
  
  return interface;

};


TTI.Widgets.TextField = function(spec) {
  /**** 
  example spec:
    { 
      label: 'Name', 
      value: 'Chris',
      type: 'text', //or number, or password 
      onChange: function(a) { console.log(a); } 
    }
 ****/   
  var type = spec.type || 'text';

  var editBox = jQuery('<input />');



  var wrap = jQuery('<div class="tti-' + type + '-field"></div>');


  //FIXME: need to decide as a rule if spec.id should go on the wrapper div or on the actually active element....
  if (spec.id) {
    wrap.attr('id',spec.id);
  }

  var interface = {};

  interface.value = spec.value;

  interface.redraw = function() {
    wrap.empty();
    interface.renderInnardsOn(wrap);
    console.log('redraw+trigger');
    editBox.trigger('change');
  };

  interface.renderInnardsOn = function(html) {
    var label = jQuery('<label></label>');
    editBox.attr('type',type);
    label.html(spec.label);
    editBox.val(interface.value);
    editBox.change(spec.onChange);
    html.append(label);
    html.append('<br />');
    html.append(editBox);
    html.append('<br />');
  };
  
  
  interface.renderOn = function(html) {
    interface.renderInnardsOn(wrap);
    html.append(wrap);
  };
  
  interface.hide = function() {
    wrap.empty();  
  };
  
  interface.show = function() {
    interface.renderInnardsOn(wrap);
  };
  
  return interface;
};


TTI.Widgets.Selector = function(spec) {
  //spec requires:
  //items - an array of objects
  //each item must have 2 properties:
  //  label 
  //  object

  var interface = {};
  
  interface.selected = spec.selected;
  
  interface.renderOn = function(html) {
  
    var wrap = jQuery('<div class="tti-selector">');

    //FIXME: need to decide as a rule if spec.id should go on the wrapper div or on the actually active element....
    if (spec.id) {
      wrap.attr('id',spec.id);
    }

    var title = jQuery('<div class="title">');
    title.html(spec.title);
    var ul = jQuery('<ul>');

    spec.items.each(function(item) {
      var li = jQuery('<li>');
      li.html(item.label);     
      li.click(function() {
        ul.find('li').removeClass('selected');
        li.addClass('selected');
        
        interface.selected = item.object;
        spec.onSelect(interface.selected);
      });
      if (item.object == interface.selected) {
        li.addClass('selected');
      }
      ul.append(li);
    });
    wrap.append(title);
    wrap.append(ul);
    html.append(wrap);
  };
  return interface;
};


TTI.Widgets.UpdateIncidentForm = function(spec) {
  var incident = spec.incident;
  var interface = {}; 

  interface.renderOn = function(html) {
  
    var statusItems = ['Unknown','Detected','Verified','Moved','Cleared','Queue Cleared'].map(function(s) {
      return { label: s, object: s };
    });
  
      var statusSelector = TTI.Widgets.Selector({
        title: 'Status (required)',
        items: statusItems,
      selected: incident.status,
      onSelect: function(item){
        incident.status = item;
      }
    });
    statusSelector.renderOn(html);
  
  
    var descriptionLabel = jQuery('<label>Description</label>');
    html.append(descriptionLabel);
    
    var descriptionTextarea = jQuery('<textarea></textarea>');
    html.append(descriptionTextarea);
    
    
    
    
  
    var updateButton = jQuery('<button class="yellow update">Update Incident</button>');
    updateButton.click(function() {
      incident.description = descriptionTextarea.val();
      incident.update();
      spec.onClose();
    });
    
    html.append(updateButton);
    
    var cancelButton = jQuery('<button class="cancel">Cancel</button>');
    cancelButton.click(function() {
      incident.status = incident.oldStatus; //restore old value
      spec.onClose();
    });
    html.append(cancelButton);
  };
  
  return interface;
};

TTI.Widgets.DropdownField = function(spec) {
  var wrap = jQuery('<div class="tti-dropdown-field"></div>');
  if (spec.id) {
    wrap.attr('id',spec.id);
  }

  var elem = jQuery('<select></select>');

  var interface = {};

  interface.selected = spec.selected;
  interface.options = spec.options;

  interface.redraw = function() {
    interface.selected = false;
    wrap.empty();
    interface.renderInnardsOn(wrap);
    
    if (interface.options.length > 0) {
      interface.selected = interface.options[0];   
    }
    elem.trigger('change');
  };

  interface.renderInnardsOn = function(html) {
    var label = jQuery('<label></label>');

    interface.options.each(function(item){
      var opt = jQuery('<option></option>');

      opt.html(item.label);
      if (item.object == interface.selected) {
        opt.attr('selected','selected');
      }
      opt.click(function() {
        console.log('click');
      });     
      opt.change(function() {
        console.log('opt-change');
      });     
      
      elem.append(opt);
    });
    
    
    label.html(spec.label);
   
    if (spec.cssClass) {
      editBox.addClass(spec.cssClass);       
    }
    elem.change(function() {
      var idx = this.selectedIndex;
      spec.onChange(interface.options[idx].object);
    });
    
    
    html.append(label);
    html.append('<br />');
    html.append(elem);
    html.append('<br />');
  };

  interface.renderOn = function(html) {
    interface.renderInnardsOn(wrap);
    html.append(wrap);
  };
  
  interface.hide = function() {
    wrap.empty();  
  };
  
  interface.show = function() {
    interface.renderInnardsOn(wrap);
  };

  return interface;
};





TTI.Widgets.NewIncidentForm = function(spec) {

  var dateString = (new Date()).toString();
  var incident = TTI.Incident({  
    affected_connector: false,
    affected_entrance_ramp: false,
    affected_exit_ramp: false,
    affected_hov_lane: false,
    affected_lane1: false,
    affected_lane2: false,
    affected_lane3: false,
    affected_lane4: false,
    affected_lane5: false,
    affected_lane6: false,
    affected_lane7: false,
    affected_lane8: false,
    affected_left_shoulder: false,
    affected_right_shoulder: false,
    affected_unknown: false,
    cross_street: '',
    description: '',
    direction: 'Northbound',
    latitude: 0,
    longitude: 0,
    mile_marker: 0,
    mile_marker_adjust: 'At',
    roadway: 'Freeway (I-35)',
    severity: 'Minor',
    status: 'Detected',
    vehicle_count: 'Single'
  });
  incident.cross_street = '';


    var mileMarkerTextbox = TTI.Widgets.TextField({
      label: 'Mile Marker (required)',
      value: 0,
      type: 'number',
      id: 'mile-marker',
      onChange: function(x) { 
        ///console.log('x',x); 
        /////console.log(this.value);
        incident.mile_marker = this.value;
        console.log(incident);
        refreshCrossStreets(this.value);
      }
    });
  
  
  //FIXME: label vs title for DropdownField spec ?
  var crossStreetDropdown = TTI.Widgets.DropdownField({
    ////label: 'Cross Street (required)',
    options: [],
    id: 'cross-street',
    ///selected: '',
    onChange: function(item){
      console.log('cs itemmmmmyxyxyxy',item);
      incident.cross_street = item.cross_street;
      incident.latitude = item.latitude;
      incident.longitude = item.longitude;
    }
  });


  var useMyLocationButton = jQuery('<button id="use-my-location">Use My Location!</button>');
  var myLocationError = jQuery('<div id="my-location-error">&nbsp;</div>');
  
  
  function locationsNearMile(mile) {
    var them = eval(jQuery.ajax({ 
      url: TTI.baseURL + '/ws', 
      type: 'POST',
      data: { "action": 'locations_near_mile', "mile": mile }, 
      async: false
      }).responseText);
    console.log('them',them);         
    return them;   
  }

  
  function refreshCrossStreets(mile) {     
    
    

    
    /////console.log('streets',streets);
    
    ///redraw() should take care of this////crossStreetDropdown.empty(); 
    
    var locations = locationsNearMile(mile).collect(function(location) {
      return { label: location.cross_street, object: location };
    });
    
    crossStreetDropdown.options = locations;
    crossStreetDropdown.redraw();
  }
  
  var interface = {}; 


  interface.validate = function() {
    if (incident.cross_street == '') {
      ///return 'invalid cross street';
    }
    if (incident.mile_marker == '') {
      return 'invalid mile marker';
    }
    if (incident.status == '') {
      return 'invalid status';
    }
    return true;
  
  };
  
  
  interface.findMe = function() {

    if (!navigator.geolocation) { return false; }
    
    navigator.geolocation.getCurrentPosition(function(position){
      var lat = position.coords.latitude;
      var lon = position.coords.longitude;  
    
      ///console.log(lat,lon);
      
      var myPoint = TTI.Point({ x: lat, y: lon });    //actually uses the device's location
      var closest = TTI.pointClosestTo(myPoint);
      
      
      var closestDistance = myPoint.distanceTo(closest);
      
      ///console.log('my',myPoint,'closest',closest);
  
      /***
      m271.distanceTo(m275)
      0.055525562581570403
      ***/
      var tolerance = 0.0555; //FIXME: may need to tweak this
      //var tolerance = 1000.0555; //very high tolerance for testing
      
      if (closestDistance > tolerance) {
        myLocationError.html("Sorry, I don't know of any I-35 Mile Markers near your location ({lat},{long})".supplant({
          lat: myPoint.x,
          long: myPoint.y
        }));
        return false;
      }
  
    

      ///var myPoint = TTI.Point({ x: 31.5, y: -97.1 });
      ///console.log(myPoint,'myPoint');
      ////console.log('nearest to myPoint: ',closest);
      mileMarkerTextbox.value = closest.mile;
      mileMarkerTextbox.redraw();
      ///////(closest.mile).select();
        
    },function(error){
        //use error.code to determine what went wrong
    });  
  };

  interface.renderOn = function(html) {

    var title = jQuery('<h3>Report an Incident</h3>');
    html.append(title);
    
    var instructions = jQuery('<p>Please fill out all required fields below and press the "Report Incident" button.</p>');
    html.append(instructions);

    var leftPanel = jQuery('<div class="panel left-panel"></div>');
    var rightPanel = jQuery('<div class="panel left-panel"></div>');

    html.append(leftPanel);
    html.append(rightPanel);

    var roadwayItems = ['Freeway (I-35)','Frontage Road','Ramp'].map(function(s) {
      return { label: s, object: s };
    });
    incident.roadway = roadwayItems[0].object;
    var roadwaySelector = TTI.Widgets.Selector({
      title: 'Roadway (required)',
      items: roadwayItems,
      selected: incident.roadway,
      onSelect: function(item){
        incident.roadway = item;
      }
    });
    roadwaySelector.renderOn(leftPanel);

    mileMarkerTextbox.renderOn(leftPanel);

    useMyLocationButton.click(function() {
      console.log('calling interface.findMe()');
      interface.findMe();  
    });
    
    leftPanel.append(useMyLocationButton);
    leftPanel.append(myLocationError);
    
    var mileMarkerAdjustItems = ['Before','At','After'].map(function(s) {
      return { label: s, object: s };
    });
    incident.mile_marker_adjust = 'At'; 
    var mileMarkerAdjustDropdown = TTI.Widgets.DropdownField({
      title: 'Cross Street (required)',
      id: 'mile-marker-adjust',
      options: mileMarkerAdjustItems,
      selected: incident.mile_marker_adjust,
      onChange: function(item){
        //console.log('itemmmmm',item);
        incident.mileMarkerAdjust = item;
      }
    });
    mileMarkerAdjustDropdown.renderOn(leftPanel);



    //crossStreetDropdown declared outside of renderOn
    crossStreetDropdown.renderOn(leftPanel);

    var directionItems = ['Northbound','Southbound'].map(function(s) {
      return { label: s, object: s };
    });
    incident.direction = directionItems[0].object;
    var directionDropdown = TTI.Widgets.DropdownField({
      label: 'Direction',
      options: directionItems,
      selected: incident.direction,
      onChange: function(item){
        //console.log('itemmmmm',item);
        incident.direction = item;
      }
    });
    directionDropdown.renderOn(leftPanel);
    
    var vehicleCountItems = ['Single','Multiple'].map(function(s) {
      return { label: s, object: s };
    });
    incident.vehicle_count = vehicleCountItems[0].object;
    var vehicleCountSelector = TTI.Widgets.Selector({
      title: 'Vehicle Count',
      items: vehicleCountItems,
      selected: incident.vehicle_count,
      onSelect: function(item){
        console.log('vehicle count changed',item);
        incident.vehicle_count = item;
      }
    });
    vehicleCountSelector.renderOn(leftPanel);


    var severityItems = ['Minor','Major'].map(function(s) {
      return { label: s, object: s };
    });
      var severitySelector = TTI.Widgets.Selector({
        title: 'Severity',
        items: severityItems,
      selected: incident.severity,
      onSelect: function(item){
        incident.severity = item;
      }
    });
    severitySelector.renderOn(leftPanel);



    /*************************************
    NOTE: shouldn't need a status selector on the creation of a new incident...should always be Detected, per our discussion
    
    var statusItems = ['Unknown','Detected','Verified','Moved','Cleared','Queue Cleared'].map(function(s) {
      return { label: s, object: s };
    });
      incident.status = 'Detected';
      var statusSelector = TTI.Widgets.Selector({
        title: 'Status (required)',
        items: statusItems,
      selected: incident.status,
      onSelect: function(item){
        incident.status = item;
      }
    });
    statusSelector.renderOn(leftPanel);
    *************************************/


    rightPanel.append(jQuery('<label>Affected Lanes</label>'));
    [
      { label: 'Unknown', id: 'affected-unknown', property: 'affected_unknown'},
      { label: 'Left Shoulder', id: 'affected-left-shoulder', property: 'affected_left_shoulder'},
      { label: 'Lane 1', id: 'affected-lane-1', property: 'affected_lane1'},
      { label: 'Lane 2', id: 'affected-lane-2', property: 'affected_lane2'},
      { label: 'Lane 3', id: 'affected-lane-3', property: 'affected_lane3'},
      //{ label: 'Lane 4', id: 'affected-lane-4', property: 'affected_lane4'},
      //{ label: 'Lane 5', id: 'affected-lane-5', property: 'affected_lane5'},
      //{ label: 'Lane 6', id: 'affected-lane-6', property: 'affected_lane6'},
      //{ label: 'Lane 7', id: 'affected-lane-7', property: 'affected_lane7'},
      //{ label: 'Lane 8', id: 'affected-lane-8', property: 'affected_lane8'},
      { label: 'Right Shoulder', id: 'affected-right-shoulder', property: 'affected_right_shoulder'},
      { label: 'Entrance Ramp', id: 'affected-entrance-ramp', property: 'affected_entrance_ramp'},
      { label: 'Exit Ramp', id: 'affected-exit-ramp', property: 'affected_exit_ramp'}

      //don't forget to add or remove the comma!!!      
      
      //{ label: 'Connector', id: 'affected-connector', property: 'affected_connector'},
      //{ label: 'HOV Lane', id: 'affected-hov', property: 'affected_hov_lane'}
    ].each(function(field) {

      var checkbox = TTI.Widgets.Checkbox({
        label: field.label,
        id: field.id,
        onChange: function() {
          incident[field.property] = this.checked; 
        }
      });
      checkbox.renderOn(rightPanel);
    });
    

    
    
    
  
    html.append('<div style="clear: both;">&nbsp;</div>'); //clear div

  
    var descriptionLabel = jQuery('<label>Description</label>');
    html.append(descriptionLabel);
    
    incident.description = '';
    var descriptionTextarea = jQuery('<textarea></textarea>');
    html.append(descriptionTextarea);
    descriptionTextarea.change(function() {
      incident.description = this.value;
    });
  
    var saveButton = jQuery('<button class="yellow update">Report Incident</button>');
    saveButton.click(function() {
      var validation = interface.validate();
      if (validation !== true) {
        alert(validation);        
        return false;
      }      

      incident.save(); //calling AJAX report_incident
      spec.onClose();
    });
    
    html.append(saveButton);
    
    var cancelButton = jQuery('<button class="cancel">Cancel</button>');
    cancelButton.click(function() {
      spec.onClose();
    });
    html.append(cancelButton);
  };
  
  return interface;
};

