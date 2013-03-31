
/* sprintf */
var formats = { 
	'b': function(val) {return parseInt(val, 10).toString(2);}, 
	'c': function(val) {return String.fromCharCode(parseInt(val, 10));}, 
	'd': function(val) {return parseInt(val, 10);}, 
	'u': function(val) {return Math.abs(val);}, 
	'f': function(val, p) { 
		p = parseInt(p, 10);
		val = parseFloat(val); 
		if(isNaN(p && val)) { 
			return NaN; 
		} 
		return p && val.toFixed(p) || val; 
	}, 
	'o': function(val) {return parseInt(val, 10).toString(8);}, 
	's': function(val) {return val;}, 
	'x': function(val) {return ('' + parseInt(val, 10).toString(16)).toLowerCase();}, 
	'X': function(val) {return ('' + parseInt(val, 10).toString(16)).toUpperCase();} 
 };



/*sprintf*/
var re = /%(?:(\d+)?(?:\.(\d+))?|\(([^)]+)\))([%bcdufosxX])/g; 
var dispatch = function(data){ 
	if(data.length == 1 && typeof data[0] == 'object') { //python-style printf 
		data = data[0]; 
		return function(match, w, p, lbl, fmt, off, str) { 
			return formats[fmt](data[lbl]); 
		}; 
	} else { // regular, somewhat incomplete, printf 
		var idx = 0;  
		return function(match, w, p, lbl, fmt, off, str) { 
			if(fmt == '%') { 
				return '%'; 
			} 
			return formats[fmt](data[idx++], p); 
		}; 
	} 
}; 
$(document).ready(function() {

	jQuery.extend({ 
		sprintf: function(format) { 
			var argv = Array.apply(null, arguments).slice(1); 
			return format.replace(re, dispatch(argv)); 
		}, 
		vsprintf: function(format, data) { 
			return format.replace(re, dispatch(data)); 
		}
	});
});