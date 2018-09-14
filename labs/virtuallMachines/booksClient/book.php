<?php

$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'], $query);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/mySlim/people/'.$query['id']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$book = json_decode($response, true);
?>
<h1>Name: <?php echo $book['name'];?></h1>
<h2>Year: <?php echo $book['year'];?></h2>
<h3>Author: <?php echo $book['author'];?></h3>

<a href="http://192.168.33.10/slimbooksClient">Back To List</a>
