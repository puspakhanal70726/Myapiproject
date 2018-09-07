<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<?php

//Initialize cURL.
$ch = curl_init();

//Set the URL that you want to GET by using the CURLOPT_URL option.
curl_setopt($ch, CURLOPT_URL, 'http://localhost/firstSlim/$books');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$people = json_decode($response, true);
?>
<h1>Books</h1>
      <table class='table'>
      <tr><th>Name</th><th>Year</th><th>Author<th>Actions</th></th>
<?php
foreach($books as $book) {
  echo "<tr>
        <td>".$book["name"]."</td><td>".$book["year"]."</td><td>".$book["author"]."</td>
        <td><a href='http://localhost:8080/exampleClient/books.php?id=".$book["id"]."'>Details</a></td>
        </tr>";
}
echo "</table>";
