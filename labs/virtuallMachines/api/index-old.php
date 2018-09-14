<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require './vendor/autoload.php';
$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = 'root';
$config['db']['dbname'] = 'apidb';
$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
// test function i use to make sure app is working, returns Hello, ______
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});
// gets all books from the database
$app->get('/names', function (Request $request, Response $response) {
    $this->logger->addInfo("GET /names");
    //query to select all the rows within the books table
    $books = $this->db->query('SELECT * from names')->fetchAll();
    $jsonResponse = $response->withJson($names);
    return $jsonResponse;
});
/*
Function will find a books from the table based on the id given
curl http://192.168.33.10/api/$books/1
*/
$app->get('/names/{id}', function (Request $request, Response $response, array $args) {
    // gets id from the parameters to use for the query
    $id = $args['id'];
    $this->logger->addInfo("GET /names/".$id);
    // query
    $books = $this->db->query('SELECT * from names where id='.$id)->fetch();
    $jsonResponse = $response->withJson($names);
    return $jsonResponse;
});
/*
Function will delete a books from the table from the id given
curl -X DELETE  http://192.168.33.10/api/books/2
*/
$app->delete('/names/{id}', function (Request $request, Response $response, array $args) {
  $id = $args['id'];
  $this->logger->addInfo("DELETE /names/".$id);
  $books = $this->db->exec('DELETE FROM names where id='.$id);
  $jsonResponse = $response->withJson($names);
  return;
});
/*
Function will find the books based on the given id then update the different fields with the provided information
Test curl for the update function:
curl -X PUT \
 http://192.168.33.10/api/books/1 \
 -H 'Cache-Control: no-cache' \
 -H 'Content-Type: application/x-www-form-urlencoded' \
 -H 'Postman-Token: a23837f2-2b01-4776-89a8-8b528bd94aec' \
 -d 'name=StarCraft&year=1998&console=PC'
*/
$app->put('/names/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $this->logger->addInfo("PUT /names/".$id);
    // build query string
    $updateString = "UPDATE names SET ";
    $fields = $request->getParsedBody();
    $keysArray = array_keys($fields);
    $last_key = end($keysArray);
    foreach($fields as $field => $value) {
      $updateString = $updateString . "$field = '$value'";
      if ($field != $last_key) {
        // conditionally add a comma to avoid sql syntax problems
        $updateString = $updateString . ", ";
      }
    }
    $updateString = $updateString . " WHERE id = $id;";
    // execute query
    $this->db->exec($updateString);
    // return updated record
    $books = $this->db->query('SELECT * from names where id='.$id)->fetch();
    $jsonResponse = $response->withJson($books);
    return $jsonResponse;
});
/*
Function will add a row to the books table with the information provided.
Similar to the update function, it constructs an INSERT query based on the data
sent to it
Test curl for adding a new books, id may need to be changed
curl -X POST \
 http://192.168.33.10/api/bookss \
 -H 'Cache-Control: no-cache' \
 -H 'Content-Type: application/x-www-form-urlencoded' \
 -H 'Postman-Token: a23837f2-2b01-4776-89a8-8b528bd94aec' \
 -d 'id=8&name=Doom&year=2016&console=PS4'
*/
$app->post('/names', function (Request $request, Response $response, array $args) {
    $this->logger->addInfo("POST /names");
    $addString = "INSERT INTO names ";
    $addString = $addString . "(";
    $fields = $request->getParsedBody();
    $keysArray = array_keys($fields);
    $last_key = end($keysArray);
    // first loop adds the different column names
    foreach($fields as $field => $value) {
      $addString = $addString . "$field";
      if( $field != $last_key) {
        // adds the comma between values
        $addString = $addString . ", ";
      }
    }
    $addString = $addString . ") VALUES (";
    // second loop adds the values for each of the columns
    foreach($fields as $field => $value) {
      //adds quotes for the 2 strings that could be used for the query
      if($field == "name" || $field == "console") {
        $addString = $addString . " '$value' ";
      }
      //numbers
      else {
        $addString = $addString . "$value";
      }
      if($field != $last_key) {
        $addString = $addString . ", ";
      }
    }
    // closes the create query from the information sent
    $addString = $addString . ");";
    // query is executed
    $this->db->exec($addString);
});
$app->run();
