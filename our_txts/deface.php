<?php
$myfile = fopen("../../../index2.html", "w") or die("Unable to open file!");
$txt = '<!DOCTYPE html>
<html>
<body style="background-color:black;">

<h1 style="text-align:center; font-size:50px; color:red">Coach == Admin</h1>

<h3 style="text-align:center;"><iframe width="600" height="500" src="https://img-9gag-fun.9cache.com/photo/aVwzW6O_460svvp9.webm">
</iframe></h3>

<h1 style="text-align:center; font-size:50px; color:red">BitsPlease</h1>

</body>
</html>
';
fwrite($myfile, $txt);
fclose($myfile);
echo "done";
?> 
