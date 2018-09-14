<?php
namespace puspa\api;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require './vendor/autoload.php';
class App
{
  private $app;
  public function __construct($db) {

    $config['db']['host']   = 'localhost';
    $config['db']['user']   = 'root';
    $config['db']['pass']   = 'root';
    $config['db']['dbname'] = 'apidb';

    $app = new \Slim\App(['settings' => $config]);

    $container = $app->getContainer();
    $container['db'] = $db;

    $container['logger'] = function($c) {
      $logger = new \Monolog\Logger('my_logger');
      $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
      $logger->pushHandler($file_handler);
      return $logger;
    };

    // test function i use to make sure app is working, returns Hello, ______
    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
      $name = $args['name'];
      $this->logger->addInfo('get request to /hello/'.$name);
      $response->getBody()->write("Hello, $name");
      return $response;
    });

    // gets all books from the database
    $app->get('/books', function (Request $request, Response $response)
     {
      //query to select all the rows within the books table
      $books = $this->db->query('SELECT * from books')->fetchAll();
      $jsonResponse = $response->withJson($books);
      return $jsonResponse;
    });

    /*
    Function will find a books from the table based on the id given

    curl http://192.168.33.10/api/books/1
    */
    $app->get('/books/{id}', function (Request $request, Response $response, array $args) {
      // gets id from the parameters to use for the query
      $id = $args['id'];
      $this->logger->addInfo("GET /$books/".$id);
      // query
      $books = $this->db->query('SELECT * from books where id='.$id)->fetch();

      if($books){
        $response =  $response->withJson($books);
      } else {
        $errorData = array('status' => 404, 'message' => 'not found');
        $response = $response->withJson($errorData, 404);
      }
      return $response;
    });

    /*
    Function will delete a books from the table from the id given

    curl -X DELETE  http://192.168.33.10/api/books/2
    */
    $app->delete('/books/{id}', function (Request $request, Response $response, array $args) {
      $id = $args['id'];
      $this->logger->addInfo("DELETE /books/".$id);
      $books = $this->db->exec('DELETE FROM books where id='.$id);
      if($books){
        $response = $response->withStatus(200);
      } else {
        $errorData = array('status' => 404, 'message' => 'not found');
        $response = $response->withJson($errorData, 404);
      }
      return $response;
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
    $app->put('/books/{id}', function (Request $request, Response $response, array $args) {
      $id = $args['id'];
      $this->logger->addInfo("PUT /$books/".$id);

      // checks if books exists
      $books = $this->db->query('SELECT * from books where id='.$id)->fetch();
      if(!$books){
        $errorData = array('status' => 404, 'message' => 'not found');
        console.log($id);
        $response = $response->withJson($errorData, 404);
        return $response;
      }

      // books exists, time to build query string
      $updateString = "UPDATE books SET ";
      $fields = $request->getParsedBody();
      $keysArray = array_keys($fields);
      $last_key = end($keysArray);
      foreach($fields as $field => $value) {
        $updateString = $updateString . "$field = '$value'";
        if ($field != $last_key) {
          // add comma
          $updateString = $updateString . ", ";
        }
      }
      $updateString = $updateString . " WHERE id = $id;";

      // execute query
      try {
        $this->db->exec($updateString);
      } catch (\PDOException $e) {
        $errorData = array('status' => 400, 'message' => 'Invalid data provided to update');
        echo ("hi there");
        return $response->withJson($errorData, 400);
      }

      // return updated record
      $books = $this->db->query('SELECT * from books ORDER BY id desc LIMIT 1')->fetch();
      $jsonResponse = $response->withJson($books);
      return $jsonResponse;
    });

    $app->post('/books', function (Request $request, Response $response) {
        $this->logger->addInfo("POST /books/");

        // build query string
        $createString = "INSERT INTO books ";
        $fields = $request->getParsedBody();
        $keysArray = array_keys($fields);
        $last_key = end($keysArray);
        $values = '(';
        $fieldNames = '(';
        foreach($fields as $field => $value) {
          $values = $values . "'"."$value"."'";
          $fieldNames = $fieldNames . "$field";
          if ($field != $last_key) {
            // conditionally add a comma to avoid sql syntax problems
            $values = $values . ", ";
            $fieldNames = $fieldNames . ", ";
          }
        }
        $values = $values . ')';
        $fieldNames = $fieldNames . ') VALUES ';
        $createString = $createString . $fieldNames . $values . ";";
        // execute query
        try {
          $this->db->exec($createString);
        } catch (\PDOException $e) {
          var_dump($e);
          $errorData = array('status' => 400, 'message' => 'Invalid data provided to add books');
          return $response->withJson($errorData, 400);
        }
        // return updated record
        $books = $this->db->query('SELECT * from books ORDER BY id desc LIMIT 1')->fetch();
        $jsonResponse = $response->withJson($books);

        return $jsonResponse;
    });



    $this->app = $app;
  }
  public function get()
  {
    return $this->app;
  }
}
