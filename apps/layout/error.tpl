<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
<title>Error 404</title>
<style>
body{margin:80px auto;font-family:Tahoma,Verdana,Arial,sans-serif;background:Olivedrab;color:#fff;width:800px}
h1,h2{margin:0}
h1{font-size:120px;margin:30px 0}
h2{font-size:60px;margin:30px 0}
a{color:#fff}
</style>
</head>
<body>
<h1>Oops!</h1>
<h2>{=str_replace('_',' ',info.method)}</h2>
{*<p>You have tried to access a page which doesn't exist or has been removed. It's not our fault.</p>*}
{*<p><em>Faithfully yours, <a href="http://cloulu.com">cloulu.com</a></em></p>*}
	<table border=1>
		<tr>
			<td>message</td>
			<td>{=info.message}</td>
		</tr>
		<tr>
			<td>error</td>
			<td>{=info.method}</td>
		</tr>		


		<tr>
			<td>folder</td>
			<td>{=info.trace.folder}</td>
		</tr>		
		<tr>
			<td>file</td>
			<td>{=info.trace.file}</td>
		</tr>		
		<tr>
			<td>class</td>
			<td>{=info.trace.class}</td>
		</tr>		
		<tr>
			<td>method</td>
			<td>{=info.trace.method}</td>
		</tr>		
	</table>
</body>
</html>
