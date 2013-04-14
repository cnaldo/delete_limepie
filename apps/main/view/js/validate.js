$(document).ready(function() {
	if(jQuery.validator) {
		//window[ 'remote_callback' ] = function( response ) {
		//	return response;
		//};

		jQuery.validator.addMethod("match", function(value, element, param) {
			var reg = new RegExp("^"+param+"$","gi");
			return this.optional(element) || reg.test(value);
		}, "");

		// http://docs.jquery.com/Plugins/Validation/Methods/minlength
		jQuery.validator.addMethod("minlength", function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : this.getLength($.trim(value), element);
			return this.optional(element) || length >= param;
		}, "");

		// http://docs.jquery.com/Plugins/Validation/Methods/maxlength
		jQuery.validator.addMethod("maxlength", function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : this.getLength($.trim(value), element);
			return this.optional(element) || length <= param;
		}, "");

		// http://docs.jquery.com/Plugins/Validation/Methods/rangelength
		jQuery.validator.addMethod("rangelength", function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : this.getLength($.trim(value), element);
			return this.optional(element) || ( length >= param[0] && length <= param[1] );
		}, "");

		// http://docs.jquery.com/Plugins/Validation/Methods/minlength
		jQuery.validator.addMethod("mincount", function( value, element, param ) {
			var length = $.findByNameCount(element.name);
			return length >= param;
		}, "");

		// http://docs.jquery.com/Plugins/Validation/Methods/maxlength
		jQuery.validator.addMethod("maxcount", function( value, element, param ) {
			var length = $.findByNameCount(element.name);
			return length <= param;
		}, "");

		// http://docs.jquery.com/Plugins/Validation/Methods/rangelength
		jQuery.validator.addMethod("rangecount", function( value, element, param ) {
			var length = $.findByNameCount(element.name);
			return ( length >= param[0] && length <= param[1] );
		}, "");		

		jQuery.validator.addMethod("remote", function(value, element, param) {

			if ( this.optional(element) ) {
				return "dependency-mismatch";
			}
			var previous = this.previousValue(element);
			if (!this.settings.messages[element.name] ) this.settings.messages[element.name] = {};
			previous.originalMessage = this.settings.messages[element.name].remote;
			this.settings.messages[element.name].remote = previous.message;
					
			if(typeof previous.ret == 'undefined') previous.ret = false;
			var data = {};

			data[element.name] = value;

			var tmp = param.add || [];
			for(i=0,j=tmp.length;i<j;i++) {
				var k = $.findByName(tmp[i]);
				if(k) {
					data[tmp[i]] = k.val();
				}
			}

			if ( previous.old !== value ) {
				previous.old = value;
				var response = json_dec($.ajax({
					url		: param.url || param,
					type	: 'post',
					mode	: "abort",
					async	: false,
					dataType: "json",
					data	: data
				}).responseText);

				previous.ret = response.result.count > 0 ? false : true;
				return previous.ret;
			}
			return previous.ret;
		}, "");

		jQuery.validator.messages = {
			maxlength	: $.validator.format(_t("{0} 자 이내로 입력하세요.")),
			minlength	: $.validator.format(_t("{0} 자 이상 입력하세요.")),
			rangelength	: $.validator.format(_t("{0} ~ {1}자 길이의 문자를 입력하십시오.")),
			range		: $.validator.format(_t("{0}, {1} 사이의 값을 입력하십시오.")),
			max			: $.validator.format(_t("{0} 이하의 값을 입력하세요.")),
			min			: $.validator.format(_t("{0} 이상의 값을 입력하세요.")),
			match		: _t("형식이 일치하지 않습니다."),
			required	: _t("이 필드는 필수입니다."),
			remote		: _t("이 필드를 수정하십시오."),
			email		: _t("유효한 E-메일 주소를 입력하십시오."),
			url			: _t("유효한 URL을 입력하십시오."),

			equalTo		: $.validator.format(_t("{0} 항목과 동일한 값을 입력하십시오.")),
			
			date		: _t("유효한 날짜를 입력하십시오."),
			dateISO		: _t("유효 날짜 (ISO)를 입력하십시오."),
			number		: _t("유효한 숫자를 입력하세요."),
			digits		: _t("숫자만 입력하세요."),
			creditcard	: _t("유효한 신용 카드 번호를 입력하십시오."),
			accept		: _t("허용되지 않는 확장자입니다.")
		};

		var __submitted = true;
		jQuery.validator.setDefaults({
			ignoreTitle		: true,
			focusInvalid	: true,
			onsubmit		: false,
			onkeyup			: false,
			onclick			: false,
			onfocusout		: false,
			showErrors		: function(errorMap, errorList){

				if(__submitted == true) {
					if( errorList && errorList[0] && errorList[0].element) {
						//$(errorList[0].element).alertBubbleBox(errorList[0].message);
						alert(errorList[0].message);
						$(errorList[0].element).focus();
					}
					__submitted	= false;
					__submit	= false;
				}
			},
			success		: function(ele) {
				//alert(9);
				var tmp = ele.parents()
				tmp.find("div.errors").remove();
			}
		});
		jQuery.fn.checkform = function(filter) {
			if($.data(this[0], 'validator')) {
			} else {
				$(this).validate(filter);
			}
			__submitted = true;
			return this.valid();
		};
	}



	var __submit = false;/* ajax가 종료되기전까지 2중 요청 방지. button disabled하려다 button이 아닐수도 있으니..*/
	jQuery.extend({ 
		/*
		$.post_submit({
			confirm	: _t("정말 삭제하시겠습니까?"),
			action	: '//super/main.site/delete',
			data	: "site_seq="+seq,
			move	: 'reload'
		});		
		*/
		post_submit : function(options) {
			try {
				if(__submit == true) return;
				var confirm_msg	= options.confirm	|| null;
				if(confirm_msg) {
					if(!confirm(confirm_msg)) {
						return false;
					}
				}
				var move	= options.move		|| null;
				var success	= options.success	|| function(result) {
					alert(result.msg);

					if(move == 'reload') {
						document.location.reload();	
					} else if(move) {
						document.location.href = move;
					}
				};
				var error	= options.error		|| function(result) { 
					alert(result.msg);
				};
				var valid	= options.valid		|| function(result) {
					$(result.element).alertBubbleBox(result.msg);
					return true; 
				};

				$.ajax({
					type	: 'post',
					url		: options.action,
					data	: options.data || {}, 
					success : function (r) {
						if(!r || typeof r === 'undefined') {

						} else {
							if(typeof r.status === 'undefined') {
								alert(_t('validate error'));
							} else if(r.status == 'valid') {
								for(err in r.result) {
									r.result[err].element = $.findByName(r.result[err].name);
									var ret = valid(r.result[err]);
									if(true === ret) { // 제일 처음것 출력후 종료
										break;	
									};
								}
							} else if (r.status == 'error') {
								error(typeof r.result === 'undefined' ? {msg : _t('error')} : r.result);
							} else if (r.status == 'success') {
								success(typeof r.result === 'undefined' ? {msg : _t('success')} : r.result);
							}
						}
					},
					dataType:'json'
				});
			} catch (e) {
				consolelog(e);
			}
			$.delay(1000, function() {
				__submit = false;
			});
		}
	});

	jQuery.fn.form_submit = function(options) {
		try {
			if(__submit == true) return;
			if(typeof tinyMCE !== 'undefined' ) {
				tinyMCE.triggerSave();
			}
			var confirm_msg	= options.confirm	|| null;
			if(confirm_msg) {
				if(!confirm(confirm_msg)) {
					return false;
				}
			}
			var filter	= options.filter	|| {};
			var manual	= options.manual || false;

			var move	= options.move		|| null;
			var success	= options.success	|| function(result) {
				alert(result.msg);
				if(move == 'reload') {
					document.location.reload();	
				} else if(move) {
					document.location.href = move;
				}
			};
			var error	= options.error		|| function(result) { 
				alert(result.msg);
			};
			var valid	= options.valid		|| function(result) {
				$(result.element).alertBubbleBox(result.msg);
				return true; 
			};

			if(this.checkform(filter) == true) {
				this.find("div.errors").remove();

				$.ajax({
					type	: this.attr('method') || 'post',
					url		: this.attr('action'),
					data	: this.serialize(), 
					success : function (r) {
						if(!r || typeof r === 'undefined') {

						} else {
							if(typeof r.status === 'undefined') {
								alert(_t('validate error'));
							} else if(r.status == 'valid') {
								for(err in r.result) {
									r.result[err].element = $.findByName(r.result[err].name);
									var ret = valid(r.result[err]);
									if(true === ret) { // 제일 처음것 출력후 종료
										break;	
									};
								}
							} else if (r.status == 'error') {
								error(typeof r.result === 'undefined' ? {msg : _t('error')} : r.result);
							} else if (r.status == 'success') {
								success(typeof r.result === 'undefined' ? {msg : _t('success')} : r.result);
							}
						}
					},
					dataType:'json'
				});
			}		
		} catch (e) {
			consolelog(e);
		}
		//$(window).unbind('beforeunload');
		consolelog(manual);
		if(manual == false) {
			window.onbeforeunload = null;

			$.delay(1000, function() {
				__submit = false;
			});
		}
		return false;
	}



});

// 데이터 임의전송시에도 valid사용할수있게 폼생성
function $form(action, data) {
	var form = $('<form />').attr({
		method	: 'post',
		action	: action
	});

	$.each(data, function(name,value) {
		$('<input />').appendTo(form).attr({
			name	: name
		}).val(value);
	});

	return form;
}

var __submit = false;
function _submit(form, filter, success, error) {
	try {
		if(__submit == true) return;

		if(typeof form.element == 'object') {
			var obj		= form;
			var form	= obj.element;
			var filter	= obj.filter;
			var success	= obj.success	|| null;
			var valid	= obj.valid		|| null;
			var error	= obj.error		|| null;
		} else {
		}

		success = success || function(result) { 
			alert('success : ' + result.msg); 
		}
		error = error || function(result) { 
			alert('error : ' + result.msg); 
		}
		valid	= valid	  || function(result) {
			$(result.element).alertBubbleBox(result.msg);
			return true; 
		}

		//form = $(form);
		if($(form).checkform(filter) == true) {
			$(form).find("div.errors").remove();
			form = $(form);
			$.ajax({
				type	: form.attr('method') ? form.attr('method') : 'post',
				url		: form.attr('action'),
				data	: form.serialize(), 
				success : function (r) {
					if(!r || r === undefined) {

					} else {
						if(r.status === undefined) {
							alert(_t('validate error'));
						} else if(r.status == 'valid') {
							for(err in r.result) {
								r.result[err].element = $.findByName(r.result[err].name);
								var ret = valid(r.result[err]);
								if(true === ret) {
									break;	
								};
							}
						} else if (r.status == 'error') {
							error(r.result !== undefined ? r.result : {msg : _t('error')});
						} else if (r.status == 'success') {
							success(r.result !== undefined ? r.result : {msg : _t('success')});
						}
					}
				},
				dataType:'json'
			});
		}		
	} catch (e) {
		consolelog(e);
	}
	return __submit = false;
}

