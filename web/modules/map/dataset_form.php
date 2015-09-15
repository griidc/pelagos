<?php
// @codingStandardsIgnoreFile
?>
<html>
<body>

<form class="cleair cmxform" id="commentForm" name="ed" action="" method="post">


<a href="javascript:void(0);" NAME="My Window Name" title=" My title here " onClick=window.open("/map/","","width=1050,height=740,left=400,top=400,toolbar=0,status=0,scollbars=1,resizable=0,location=0");><img src="images/red-dot.png"> [Map]</a>


           <textarea name="geoloc"<?PHP if ($status != 0){echo "disabled";} ?>  id="cgeoloc"  rows=3 cols=98 onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag=="update"){echo $m[11];} ?></textarea>
</form>
</body>
</html>

