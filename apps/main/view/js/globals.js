function css_browser_selector(u){var ua=u.toLowerCase(),is=function(t){return ua.indexOf(t)>-1},g='gecko',w='webkit',s='safari',o='opera',m='mobile',h=document.documentElement,b=[(!(/opera|webtv/i.test(ua))&&/msie\s(\d)/.test(ua))?('ie ie'+RegExp.$1+((RegExp.$1=='6'||RegExp.$1=='7')?' ie67':(RegExp.$1=='8')?' ie678':'')):is('firefox/2')?g+' ff2':is('firefox/3.5')?g+' ff3 ff3_5':is('firefox/3.6')?g+' ff3 ff3_6':is('firefox/3')?g+' ff3':(/firefox\/(\d+)/.test(ua))?g+' ff'+RegExp.$1:is('gecko/')?g:is('opera')?o+(/version\/(\d+)/.test(ua)?' '+o+RegExp.$1:(/opera(\s|\/)(\d+)/.test(ua)?' '+o+RegExp.$2:'')):is('konqueror')?'konqueror':is('blackberry')?m+' blackberry':is('android')?m+' android':is('chrome')?w+' chrome':is('iron')?w+' iron':is('applewebkit/')?w+' '+s+(/version\/(\d+)/.test(ua)?' '+s+RegExp.$1:''):is('mozilla/')?g:'',is('j2me')?m+' j2me':is('iphone')?m+' iphone':is('ipod')?m+' ipod':is('ipad')?m+' ipad':is('mac')?'mac':is('darwin')?'mac':is('webtv')?'webtv':is('win')?'win'+(is('windows nt 6.0')?' vista':''):is('freebsd')?'freebsd':(is('x11')||is('linux'))?'linux':'','js']; c = b.join(' '); h.className += ' '+c; return c;};
css_browser_selector(navigator.userAgent);
//alert(css_browser_selector(navigator.userAgent));

try {
	document.execCommand("BackgroundImageCache", false, true);
} catch(err) {}

var jsload = function(src){
	var script = document.createElement('script'); 
	script.type = 'text/javascript'; 
	script.src = src;
	document.getElementsByTagName('head')[0].appendChild(script);
};

function consolelog(msg) {
	try	{
		console.log(msg);
	} catch (e) {
	}
}
/* _translations */
function _t(msgid) {
	//var _translations = {};

    if ( typeof _translations == "undefined" || typeof _translations[msgid] == "undefined") {
        return msgid;
    } else {
		return _translations[msgid];
	}
}
/**
 * @brief string prototype으로 trim 함수 추가
 **/
function trim(str) {
	return str.toString().trim();
}
String.prototype.trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
}
$(document).ready(function() {
	jQuery.ajaxSetup({
		jsonp: null,
		jsonpCallback: null
	});
	/* form reset */
	$('form').each(function(){
		this.reset(); // 폼리셋
		var form = $(this);
		form.data('initialForm', form.serialize()); // 초기값
		//form.beforeunload(); // 변동사항 체크
	});

	jQuery.extend(jQuery.fn,{ 
		delay : function(time, callback){
			jQuery.fx.step.delay = function(){};
			return $('<div />').animate({delay:1}, time, callback);
		}, findByName: function( name, sel ) {
			return $(document.getElementsByName(name), sel).map(function(index, element) {
				return element  || null;
			});
		}, unselectable: function(){
			this.bind("selectstart.jq", function(){return false;}).css({
				"MozUserSelect": "none",
				"KhtmlUserSelect": "none"
			}).get(0).unselectable = "on";
		}, selectable: function(){
			this.unbind("selectstart.jq").css({
				"MozUserSelect": "text",
				"KhtmlUserSelect": "text"
			}).get(0).unselectable = "off";
		}, autofocus : function() {
			if(this.first().autofocus!==true) {
				this.focus();
			}
			return this;
		}, tagName : function() {
			return this.each(function() {
				return this.tagName;
			});
		}, outerHTML : function(s) {
			return (s) ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
		}, trim : function(  ) {
			return $.trim(this);
		}
	});

	jQuery.expr[':'].regex = function(elem, index, match) {
		var matchParams = match[3].split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels) ? 
							matchParams[0].split(':')[0] : 'attr',
				property: matchParams.shift().replace(validLabels,'')
			},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	}

	
});
















/* in_array */
function in_array(arr, chk) {
	return arr.in_array(chk);
}
Array.prototype.in_array = function (value) {
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] === value) {
			return true;
		}
	}
	return false;
};


String.prototype.replaceAll = function(a, b) {
	return this.split(a).join(b);
}
/*
text = '123,4,4234.234,23523.2,342';
text.toInt() == 12344234
text.toNum() == 12344234.23423523
text.numberFormat() = 12,344,234.234,235,23
*/
function number_format(str) {
	return str.toString().number_format();
}
function nf(str) {
	return str.toString().number_format();
}
String.prototype.number_format = function() {
    var num = (this.to_num() + '').split(/\./);
    var commal = function(text) {
        var ret = text.replace(/(\d)(\d{3},)/g, '$1,$2');
        if(ret == text) return ret;
        return commal(ret);
    }
    var commar = function(text) {
        var ret = text.replace(/(,\d{3})(\d)/g, '$1,$2');
        if(ret == text) return ret;
        return commar(ret);
    }
    var ret = commal(num[0].replace(/(\d)(\d{3})$/g, '$1,$2'));
    if(num.length > 1) {
        ret += '.' + commar(num[1].replace(/^(\d{3})(\d)/g, '$1,$2'));
    }
    return ret;
}
function to_int(str) {
	return str.toString().to_int();
}
String.prototype.to_int = function() {
    if(/^-/.test(this)) {
        return this.replace(/\..*$/g, '').replace(/[^\d]/g, '') * -1;
    } else {
        return this.replace(/\..*$/g, '').replace(/[^\d]/g, '') * 1;
    }
}
function to_num(str) {
	return str.toString().to_num();
}
String.prototype.to_num = function() {
    if(/^-/.test(this)) {
        return this.replace(/(\.[^\.]+)\..*$/g, '$1').replace(/[^\d\.]/g, '') * -1.0;
    } else {
        return this.replace(/(\.[^\.]+)\..*$/g, '$1').replace(/[^\d\.]/g, '') * 1.0;
    }
}



/**
* bool String::bytes(void)
* 해당스트링의 바이트단위 길이를 리턴,한글2바이트
*/
String.prototype.bytes = function() {
	var str = this;
	var l = 0;
	for (var i=0; i<str.length; i++) l += (str.charCodeAt(i) > 128) ? 2 : 1;
	return l;
}

/**
* bool String::bytes(void)
* 해당스트링의 바이트단위 길이를 리턴,한글 3바이트 처리
*/
String.prototype.bytes_utf8 = function() {
	var s = this;
	// returns the php lenght of a string (bytes, not chars)
	var c, b = 0, l = s.length;
	while(l) {
		c = s.charCodeAt(--l);
		b += (c < 128) ? 1 : ((c < 2048) ? 2 : ((c < 65536) ? 3 : 4));
	};
	return b;
}; 

String.prototype.hasFinalConsonant = function(str) {
	str = this != window ? this : str;
	var strTemp = str.substr(str.length-1);
	return ((strTemp.charCodeAt(0)-16)%28!=0);
}

function josa(str,tail) {
    return (str.hasFinalConsonant()) ? tail.substring(0,1) : tail.substring(1,2);
} 

/* json decode */
function json_dec(sStr) {
	try {
	  return (new Function('', 'return ' + sStr + ';'))();
	} catch (e) {
		alert('Syntax Error: Badly formated JSON string');
		return {};
	}
}

/* rand string */
function rand_str(){
	return String((new Date()).getTime()).replace(/\D/gi,'')+Math.floor(Math.random() * 100);
}

/* pretty echo */
function pr(arr,depth) {
	var dumped_text = "";
	if(!depth) depth = 0;

	var depth_pad = "";
	for(var j=0;j<depth+1;j++) depth_pad += "    ";

	if(typeof(arr) == 'object') {
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') {
				dumped_text += depth_pad + "'" + item + "' ...\n";
				dumped_text += dump(value,depth+1);
			} else {
				dumped_text += depth_pad + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else {
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

function nl2br (str, is_xhtml) {
    var brTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ brTag +'$2');
}
