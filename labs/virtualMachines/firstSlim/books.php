<?php

$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'], $query);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/firstSlim/books/'.$query['id']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$person = json_decode($response, true);
?>
<h1>Name: <?php echo $person['name'];?></h1>
<h2>Year: <?php echo $person['year'];?></h2>
<h3>Author: <?php echo $person['Author'];?></h3>

<a href="http://192.168.33.10/slimExampleClient">Back To List</a>
