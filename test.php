<!DOCTYPE html>
<html>
<head>
<title>Hallo :D</title>
</head>

<body>
<div style="background:#FF0000; height: 100px;">
<h1>HEADER</h1>
</div>
<div style="float:left; width: 200px; background:#0ff0ff;">
<ul>
<li><a href="#">Home</a></li>
<!-- IF LOGGED_IN([USER]) == TRUE; -->
<li><a href="#">Mijn Account</a></li>
<li><a href="#">Uitloggen</a></li>
<!-- ELSE -->
<li><a href="#">Login</a></li>
<!-- END IF -->
</ul>
</div>

<div style="background:#FF0FF0;">
<!-- IF LOGGED_IN([USER]) == TRUE; -->
Welkom Maarten :)
<!-- END IF -->
</div>

<!-- START doWhile(); -->
<!-- END doWhile; -->

{test}
{test1}
{/test}
</body>
</html>
