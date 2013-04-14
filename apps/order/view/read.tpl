<script src="/vendor/js/source/jquery/jquery-1.9.1.min.js"></script>
<!--<script src="/vendor/js/source/jquery/jquery-migrate-1.1.0.min.js"></script>-->
<script src="/vendor/js/source/jquery.validate/jquery.validate.js"></script>
<!--<script src="/apps/layout/js/jquery.validate.ext.js"></script>-->
<script src="/apps/main/view/js/globals.js?ver=33"></script>
<script src="/apps/main/view/js/validate.js"></script>

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
		<fieldse
		<p>
			<input class="submit" type="submit" value="Submit"/>
		</p>
	</fieldset>
</form>