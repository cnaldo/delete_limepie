	jQuery.validator.addMethod("match", function(value, element, param) {
		var reg = new RegExp("^"+param+"$","gi");
		return this.optional(element) || reg.test(value);
	}, "");


	jQuery.validator.addMethod("equalTo", function(value, element, param) { // june $(param) => $.findByName(param)
		// bind to the blur event of the target in order to revalidate whenever the target field is updated
		// TODO find a way to bind the event just once, avoiding the unbind-rebind overhead
		var target = $.findByName(param);
		if ( this.settings.onfocusout ) {
			target.unbind(".validate-equalTo").bind("blur.validate-equalTo", function() {
				$(element).valid();
			});
		}
		return value === target.val();
	}, "");

	jQuery.validator.addMethod("remote", function(value, element, param) {
		if ( this.optional(element) )
			return "dependency-mismatch";

		var previous = this.previousValue(element);
		if (!this.settings.messages[element.name] )
			this.settings.messages[element.name] = {};
		previous.originalMessage = this.settings.messages[element.name].remote;
		this.settings.messages[element.name].remote = previous.message;

		param = typeof param == "string" && {url:param} || param; 

		if(typeof previous.ret == 'undefined') previous.ret = false;

		var data = {};
		data[element.name] = value;
		data['module_seq'] = jQuery.findByName('module_seq').val();
		if ( previous.old !== value ) {
			previous.old = value;
			return previous.ret =  eval($.ajax($.extend(true, {
				url: param,
				mode: "abort",
				async: false,
				port: "validate" + element.name,
				dataType: "json",
				data: data
			}, param)).responseText);
		}
		return previous.ret;
	}, "");
$(document).ready(function() {

	var load = false;
	jQuery.fn.extend({ 
		editor_widget : function() { 
			var valid = jQuery(this);
				var id = valid.attr('id');
			//	alert(oEditors);
			try {
				oEditors.getById[id].exec("UPDATE_IR_FIELD", []);

				if (valid.val() == "<br>") {
					valid.val('');
				}
			} catch (e) {
				//alert(pr(e))
			}
		},
		ajax: function(opt) {
			$('textarea', this).editor_widget();//textarea 업데이트

			if($.data(this[0], 'validator')) {
				var check_result = $(this).valid();
			} else {
				if(!$(this).attr('onsubmit')) {
					//$.removeData(this[0], "ajax");

					$(this).live('submit', function() {
						load = true;
						$(this).ajax(opt);
						return false;
					});
					opt.load = true;

				} else {
					load = true;
				}
				$('textarea', this).live('focusin', function(ev) {
					var id = $(this).attr('id');
					if(typeof oEditors != 'undefined' && oEditors.getById[id]) {
						ev.stopPropagation();
						oEditors.getById[id].exec("FOCUS");
					} else {
						//this.focus();
					}
				});	
				if(opt.rules ||opt.filter ) {
					if(opt.filter) {
						opt.rules =  opt.filter.filter;
						opt.messages =  opt.filter.message;
					}
					var option = {
						focusInvalid: true,
						rules		: opt.rules,// {},
						messages	: opt.messages,// {},
						errorElement: "p",
						onkeyup		: false,
						onclick		: false,
						onsubmit	: false,
						load		: opt.load
					}
					if(opt.success) {
						option.success = opt.success;	
					} else {
						option.success = function(element) {
							//console.log(element.attr("htmlfor"));
							var msg = (typeof option.messages[element.attr("htmlfor")] != 'undefined') && (option.messages[element.attr("htmlfor")].required);

							//alert(msg);
							//console.log(element.prev().children().attr('name'));
							//alert(element.prev().children().attr('name'));
							//		 element.html("&nbsp;ok!").addClass("success");
							var tmp = element.parents('td')
							tmp.find("p").remove();
							if(msg) {
								tmp.append('<p htmlfor="name" generated="true" class="error success">'+msg+' <em>..ok</em></p>');
							}
						}
					}
					if(opt.error == 'alert') {
						option.onfocusout = false;	
					}
					if(opt.error == 'alert') {
						option.showErrors = function(errorMap, errorList){
							try {
								var caption = $(errorList[0].element).attr('caption') || $(errorList[0].element).attr('name');
								alert('[' + caption + '] ' + errorList[0].message);
								$(errorList[0].element).focus();
								//var aform = $(errorList[0].element).parents('form').get(0);
							} catch (e) {
							}
							return false;
						}
					} else { //if(opt.error == 'div') {
						option.errorPlacement = opt.error || function(error, element) {
							if(element.parent().find("p").length) {
								var tmp = element.parent()
								tmp.find("p").remove();
								tmp.append(error);
							} else {
								var tmp = element.parent()
								//	alert(tmp.html());
								element.parents('td').append(error);
							}
						}
					} /*else {// 아무 반응 없게됨... 디폴트가 div여야 할듯
						option.errorPlacement = function() {
							
						};
					}*/
					validator2 = $(this).validate(option);
					var check_result = $(this).valid();

				} else {
					var check_result = true;
					var option = {}
				}
				//if(!$.browser.msie) $(this).valid();
				if('a' == option.load) { // 없어도 될듯.. 위에서 검사후 require값이 자동 세팅
					/* load 시 메세지 입력 */
					jQuery.each(option.messages, function(name) {
						var aname = document.getElementsByName(name);
						var ele = jQuery(aname);

						if(ele.length ==0 ) return true;//continue
						ele.attr('autocomplete','off');
						var txt = jQuery.findByName(name).filter(':text, :password, :file, select, textarea').length;
						if(txt && ele.val()) {
							try {
								ele.trigger('focusout');
							} catch (e) {
							}
						} else {
							if(typeof this[option.load] != 'undefined' && this[option.load]) {
								if(ele.next("p").length) {
									ele.next("p").addClass(option.load).html(this[option.load]);
								} else {
									ele.parent().append('<p htmlfor="name" generated="true" class="error '+option.load+'">'+this[option.load]+'</p>');
								}	
							} else {
								try {
									ele.trigger('focusout');
								} catch (e) {
								}
							}
						}
					});
				}
			}

			if(load ==  true && check_result == true) {
				try {
					if(typeof opt.before == 'function' ) {
						if(false === opt.before(this)) {
							return false;
						}					
					}
					(function(data) {
						var doc = jQuery(document);
						dialog = ''
					   + '<div class="dialog-all dialog-overlay"><div class="dialog-msg">전송중</div></div>'
						$(dialog).css({
							width:doc.width(),
							height:doc.height()
						}).appendTo('body');
					})({})
					var form = this;
					if(this.attr('action')) {
						$.ajax({
							async:false,
							url: this.attr('action'),
							type : this.attr('method') || 'post',
							data : this.serialize()+'&__return_type__=json',
							//dataType : 'json',
							success: function(data, type) {
								//alert(data);
								data = evalEx(data);
								jQuery('.dialog-all').remove();
								if(typeof data.result == 'undefined') {
									data.result = 'error';
								}
								if(data.result == 'success') {
									if(data.message) {
										alert(data.message);
									}
									if(typeof opt.after == 'function' ) {
										opt.after(data, type, this);
									}
								} else if(data.result == 'filter') {
									jQuery.each(data.message, function(key) {
										var a = data.message[key];

										if(a) {

											var element = jQuery.findByName(a.name, document.forms[0]);
											if(typeof a.param == 'undefined') {
												a.param = [];
											}
											//alert(this.name + ' : ' + valid.system[this.name]);
											if(this.msgno == 'required' && element.val() == '') {
												this.msgno = 'load';
											} else if(this.msgno == 'required') {
												this.msgno = 'required';
											} else {
											}
											if( opt.rules ) {

												if(typeof opt.rules[a.name][a.msgno] == 'undefined') {
													p = null;
												} else {
													p = opt.rules[a.name][a.msgno];
												}
												if( opt.messages && 
													typeof opt.messages[a.name] != 'undefined' &&
													typeof opt.messages[a.name][a.msgno] != 'undefined'
												) {
													var error = (p?$.validator.format (opt.messages[a.name][a.msgno],p):opt.messages[a.name][a.msgno]);
												} else {

													var error = (p?$.validator.format (opt.filter.system[a.msgno],p):opt.filter.system[a.msgno]);
												}
												if(opt.error == 'alert') {
													alert(error);
													element.focus();
													return false;
												} else {
													
													if(element.parent().find("p").length) {
														var tmp = element.parent()
														tmp.find("span").remove();
														tmp.find("p").remove();
														tmp.append(error);
													} else {
														var tmp = element.parent()
														tmp.find("span").remove();

														element.parents('td').append('<p class="error">'+error+'</p>');
													}
												}
											} else {
												if(typeof this.msg != 'undefined') {
													alert(this.msg);
												//	a1 =  $(document.getElementsByName(a.name), form);


													element[0].focus();
													return false;
												}
											}
											//if(i ==0 ){
											//	element.focus();
											//}
										} else {
											var element = jQuery.findByName(key);

											if(element.attr('name') && element.val() && 
												typeof opt.messages[element.attr('name')] != 'undefined' &&
												typeof opt.messages[element.attr('name')]['load'] != 'undefined') {
												var a = opt.messages[element.attr('name')]['load'];
												

												var tmp = element.parent()
													tmp.find("p").remove();
												//	tmp.find("span").remove();
												//	tmp.append(' <span style="color:blue"> ok</span>');
													tmp.append('<p class="iDesc success">'+( (a))+' <span style="color:blue">..ok</span></p>');
											} else {
												var tmp = element.parent()
													tmp.find("p").remove();
												//	tmp.find("span").remove();
												//	tmp.append(' <span style="color:blue"> ok</span>');
											}
										}
										//return false;
									});
	//							} else if(data.result == 'error') {							
								} else {
try
{
									$('<div title="알림 메세지"><div style="margin:5px 0 5px 0"><div class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></div><p>'+data.message+'</p></div></div>').dialog({
										resizable: false,
										height:115,
										width:350,
										modal: true,
										buttons: {
											'닫기': function() {
												$(this).dialog('close');
											}
										}
									});
	
}
catch (e)
{
		alert(data.message);
}

								}
							}
						});
					}
				} catch (e) {
					alert(pr(e));
				}
			} else {
				if(typeof validator2 != 'undefined') validator2.focusInvalid();
			}
			return false;
		}
	});
});