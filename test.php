<html>
<head>
  <title>gravatar.class.php test</title>
  <style>
    body { font-family:Helvetica, Verdana, sans-serif }
    h1 { font-family: Lucida Console, Monaco, monospace; font-size:125% }
    pre { padding:5px; background-color: rgb(230,230,230); border:solid gray 1px; overflow:auto; }
  </style>
</head>
<body style="">
<?php

$email = 'gravatar@knutkohl.de';

require_once 'gravatar.class.php';

$g = new Gravatar;

echo '<h1>getGravatarURL($email)</h1>',
     $g->getGravatarURL($email);

echo '<h1>getGravatarURL($email, TRUE)</h1>',
     $g->getGravatarURL($email, TRUE),
     '<br><br>',
     htmlspecialchars($g->getGravatarURL($email, TRUE));

foreach (array('json','xml','php','vcf') as $format) {
  echo '<h1>getInfo($email, \''.$format.'\')</h1>',
       $g->getInfoURL($email, $format),
       '<pre>',
       htmlspecialchars(print_r($g->getInfo($email, $format), TRUE)),
       '</pre>';
}

echo '<h1>getInfoURL($email, \'qr\')</h1>';

$url = $g->getInfoURL($email, 'qr');

echo $url,
     '<br><br>',
     '&lt;img src="'.$url.'">',
     '<br><br>',
     '<img src="'.$url.'">';

?>
</body>
</html>