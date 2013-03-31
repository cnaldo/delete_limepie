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
<h2><?php echo str_replace('_',' ',$info["method"]);?></h2>


	<table border=1>
		<tr>
			<td>message</td>
			<td><?php echo $info["message"];?></td>
		</tr>
		<tr>
			<td>error</td>
			<td><?php echo $info["method"];?></td>
		</tr>		


		<tr>
			<td>folder</td>
			<td><?php echo $info["trace"]["folder"];?></td>
		</tr>		
		<tr>
			<td>file</td>
			<td><?php echo $info["trace"]["file"];?></td>
		</tr>		
		<tr>
			<td>class</td>
			<td><?php echo $info["trace"]["class"];?></td>
		</tr>		
		<tr>
			<td>method</td>
			<td><?php echo $info["trace"]["method"];?></td>
		</tr>		
	</table>
</body>
</html>
