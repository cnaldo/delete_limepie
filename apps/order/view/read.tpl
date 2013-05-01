<script src="/vendor/js/source/jquery/jquery-1.9.1.min.js"></script>
<!--<script src="/vendor/js/source/jquery/jquery-migrate-1.1.0.min.js"></script>-->
<script src="/vendor/js/source/jquery.validate/jquery.validate.js"></script>
<!--<script src="/apps/layout/js/jquery.validate.ext.js"></script>-->
<script src="/apps/main/view/js/globals.js?ver=33"></script>
<script src="/apps/main/view/js/validate.js"></script>

<script src="/vendor/js/source/jstree.rc2/jquery.jstree.js"></script>


<a href='/build/'>aaa</a> 
<a href='/buildx/'>bbb</a>

<style type="text/css">
	#commentForm { width: 500px; }
	#signupForm label { width: 250px; display:block;float:left;}
	#commentForm label.error, #commentForm input.submit { margin-left: 253px; }
	#signupForm { width: 670px; }
	#signupForm label.error {
		margin-left: 10px;
		width: auto;
		display: inline;
	}
	#newsletter_topics label.error {
		display: none;
		margin-left: 103px;
	}
</style>

<script>
function check(form) {
	try {
		var r = {=rules};

		_submit({
			element : form,
			filter : r,
			success : function(ret) {
				alert(_t('저장되었습니다.'));
				document.location.href = client.path + '{module.module_name}/list/{selected_category_seq}';
			}
		});
	} catch (e) {
		consolelog(e);
	}

	return false;
}
</script>
<script>
jQuery(function($) {
	$('#demo3').jstree({

		"plugins" : [ "themes", "html_data", "ui", "crrm",  "dnd","types", "rules", "contextmenu" ],
		"themes" : {
			"theme" : "default",
			"dots" : true,
			"icons" : true
		},
		"types" : {
			"max_depth" : -2,
			"max_children" : -2,
			"valid_children" : [ "root" ],
			"rules" : {
				"folder" : {
					"start_drag" : true,
					"move_node" : true,
					"valid_children" : [ "folder" ]
				},
				"root" : {
					"valid_children" : [ "folder" ],
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"remove" : false						
				}
			}
		},
	});
	$('#demo3').bind("create_node.jstree",  function (e, data) {
		//var parent_seq = $(data.rslt.obj).parent().attr("id").replace("node_","");				
		//var f = $.jstree._focused();
		//f.deselect_node();
		data.rslt.obj.attr("id", "new_" + rand_str());
		data.rslt.obj.find('a').click();

	}).bind("select_node.jstree", function (event, data) {
		jQuery('.jstree-rename-input').blur();
		// `data.rslt.obj` is the jquery extended node that was clicked
		// alert(data.node.attr("id"));
	}).bind("move_node.jstree",	function (e, data) {
		//$(data.rslt.o).children().click();
		console.log('move');
	}).bind("dblclick.jstree",	function(e, data) {
		var f = $.jstree._focused();
		jQuery('#editable_input').blur();
		f.deselect_node(e.currentTarget);
		f.rename();
	}).bind("loaded.jstree",	function(event, data) {
		var f = $.jstree._focused();
		f.open_all();
	})

	;

});

function get_menu() {
	var _menu = [];

	$('#demo3').find('li').each(function() {
		var l = _menu.length

		var obj = $(this).children("a:eq(0)").clone();
		obj.children(".jstree-icon").remove();
		var element_name	= obj.text();
		var element_id		= this.id ? this.id.replace("tree_","") : 'new';

		_menu.push(
			  'tree['+element_id+'][element_id]='	+ element_id
			+'&tree['+element_id+'][parent_id]='	+ $(this).parent().parent().attr("id").replace("tree_","")
			+'&tree['+element_id+'][position_num]='	+ $(this).index()
			+'&tree['+element_id+'][element_name]='	+ element_name
		);
	});

	$.ajax({
		async	: false,	
		type	: 'POST',
		url		: './order/tree',
		data	: _menu.join("&"), 
		success	: function (r) {
			
		},
		dataType:'json'
	});		

}
</script>
<div id="demo3" class="demo">
	{=tree}
</div>
<input type='button' onclick='get_menu()'>

<form class="cmxform" id="signupForm" method="post" action="" onsubmit='return check(this)'>
	<fieldset>
		<legend>Validating a complete form</legend>
		<p>
			<label for="firstname">Firstname</label>
			<input id="firstname" name="firstname[]" type="text" /><br />
			<input id="firstname" name="firstname[]" type="text" />

		</p>
		<fieldset id="newsletter_topics">
			<legend>the demo</legend>
			<label for="topic_marketflash">
				<input type="checkbox" id="topic_marketflash" value="marketflash" name="topic[]" />
				Marketflash
			</label>
			<label for="topic_fuzz">
				<input type="checkbox" id="topic_fuzz" value="fuzz" name="topic[]" />
				Latest fuzz
			</label>
			<label for="topic_digester">
				<input type="checkbox" id="topic_digester" value="digester" name="topic[]" />
				Mailing list digester
			</label>
			<label for="topic" class="error">Please select at least two topics you'd like to receive.</label>
		</fieldset>
		<p>
			<label for="firstname">agree</label>
			<input id="agree" name="agree" type="checkbox" />

		</p>
		
		<p>
			<input class="submit" type="submit" value="Submit"/>
		</p>
	</fieldset>
</form>