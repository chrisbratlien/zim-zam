DOM = {};
DOM.element = function () {
    if (arguments.length == 0) { return "ERROR"; }

    var tag = arguments[0];
    var interface = jQuery('<' + tag + '></' + tag + '>');
    if (arguments.length == 1) {
        return interface;
    }

    var children = arguments[1];
    if (typeof children == "string" || typeof children == "number") {
        interface.html(children);
    }
    else if (typeof children == "object") { //probably another jquery object
        interface.append(children);
    }
    else {
        children.each(function (child) {
            interface.append(child);
        });
    }
    return interface;
};

DOM.li = function () {
    if (arguments.length == 0) { return DOM.element('li'); }
    return DOM.element('li', arguments[0]);
};

var elements = ['div', 'ul', 'a', 'img', 'td', 'th', 'tr','thead', 'table', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6','select','option','image'];
elements.each(function (tag) {
    DOM[tag] = function () {
        if (arguments.length == 0) { return DOM.element(tag); }
        return DOM.element(tag, arguments[0]);
    }
});

/*******
DOM.li = function () {
    if (arguments.length == 0) { return DOM.element('li'); }
    return DOM.element('li', arguments[0]);
};


DOM.a = function () {
    if (arguments.length == 0) { return DOM.element('a'); }
    return DOM.element('a', arguments[0]);
};
DOM.img = function () {
    if (arguments.length == 0) { return DOM.element('img'); }
    return DOM.element('img', arguments[0]);
};
DOM.td = function () {
    if (arguments.length == 0) { return DOM.element('td'); }
    return DOM.element('td', arguments[0]);
};
DOM.th = function () {
    if (arguments.length == 0) { return DOM.element('th'); }
    return DOM.element('th', arguments[0]);
};
DOM.tr = function () {
    if (arguments.length == 0) { return DOM.element('tr'); }
    return DOM.element('tr', arguments[0]);
};

DOM.table = function () {
    if (arguments.length == 0) { return DOM.element('table'); }
    return DOM.element('table', arguments[0]);
};
DOM.div = function () {
    if (arguments.length == 0) { return DOM.element('div'); }
    return DOM.element('div', arguments[0]);
};
**/