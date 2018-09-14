<?php
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Http\RequestBody;

require './vendor/autoload.php';

// empty class definitions for phpunit to mock.
class mockQuery {
  public function fetchAll(){}
  public function fetch(){}
};
class mockDb {
  public function query(){}
  public function exec(){}
};

class TodoTest extends TestCase
{
  protected $app;
  protected $db;

  public function setUp()
  {
    $this->db = $this->createMock('mockDb');
    $this->app = (new puspa\api\App($this->db))->get();
  }


  // test if check the hello name function works
  public function testHelloName() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI'    => '/hello/Joe',
    ]);
    $req = Request::createFromEnvironment($env);
    $this->app->getContainer()['request'] = $req;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 200);
    $this->assertSame((string)$response->getBody(), "Hello, Joe");
  }



  // test the GET books endpoint
  public function testGetBooks() {
    // expected result string
    $resultString = '[{"id":"1","name":"hello","year":"1997","author":"Ranu"},{"id":"2","name":"Mr.X","year":"2001","author":"Krishna"}]';

    // mock the query class & fetchAll functions
    $query = $this->createMock('mockQuery');
    $query->method('fetchAll')->willReturn(json_decode($resultString, true));
    $this->db->method('query')->willReturn($query);

    // mock the request environment.  (part of slim)
    $env = Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI'    => '/books',
    ]);
    $req = Request::createFromEnvironment($env);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame($resultString, (string)$response->getBody());
  }



  // Test to get a single books from the table
  public function testGetBooks() {
    // test successful request
    $resultString = '{"id":"1","name":"hello","year":"1997","author":"Ranu"},{"id":"2","name":"Mr.X","year":"2001","author":"Krishna"}';

    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(json_decode($resultString, true));
    $this->db->method('query')->willReturn($query);
    $env = Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame($resultString, (string)$response->getBody());
  }



  // This test will test whether or not we can update the information for a books within the table
  public function testUpdateBooks() {
    // expected result string
    $resultString = '{"id":"1","name":"hello","year":"1997","author":"Ranu"},{"id":"2","name":"Mr.X","year":"2001","author":"Krishna"}';

    // mock the query class & fetchAll functions
    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(json_decode($resultString, true));
    $this->db->method('query')->willReturn($query);
    $this->db->method('exec')->willReturn(true);

    // mock the request environment.  (part of slim)
    $env = Environment::mock([
      'REQUEST_METHOD' => 'PUT',
      'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $requestBody = ["name" => "Hey brother", "year" => "2007", "author" => "Ram Gopal"];
    $req =  $req->withParsedBody($requestBody);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame($resultString, (string)$response->getBody());
  }



  public function testCreateBooks() {
    // test successful request
    $resultString = '{"id":"4","name":"Heropanthi","year":"2017","author":"Govinda}';

    // mock the query class & fetchAll functions
    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(json_decode($resultString, true));
    $this->db->method('query')->willReturn($query);
    $this->db->method('exec')->willReturn(true);

    $env = Environment::mock([
      'REQUEST_METHOD' => 'POST',
      'REQUEST_URI' => '/books',
    ]);

    $req = Request::createFromEnvironment($env);
    $requestBody = ["name" => "hey brother", "year" => "2007", "author" => "Ram Gopal"];
    $req =  $req->withParsedBody($requestBody);
    $this->app->getContainer()['request'] = $req;

    $response = $this->app->run(true);
    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame($resultString, (string)$response->getBody());
  }



  // Function will test whether the delete function is working for the api
  public function testDeleteBooks() {
    $query = $this->createMock('mockQuery');
    $this->db->method('exec')->willReturn(true);
    $env = Environment::mock([
      'REQUEST_METHOD' => 'DELETE',
      'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(200, $response->getStatusCode());
  }



  // fuction will deal with error handling when it comes to finding a books by its id
  public function testGetBooksFailed() {
    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(false);
    $this->db->method('query')->willReturn($query);
    $env = Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame($response->getStatusCode(), 404);
    $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
  }



  // function deals with the error handling if the wrong data is sent when api is trying to update a books
  public function testUpdateBooksFailed() {
    // expected result string
    $resultString = '{"id":"1","name":"hello","year":"1999","author":"Ranu"}';

    // mock the query class & fetchAll functions
    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(json_decode($resultString, true));
    $this->db->method('query')->willReturn($query);
    //mocks where the update failed!!!
    $this->db->method('exec')->will($this->throwException(new PDOException()));

    // mock the request environment.  (part of slim)
    $env = Environment::mock([
    'REQUEST_METHOD' => 'PUT',
    'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $requestBody = ["name" => "hello", "year" => "1999", "author" => "Ranu"];
    $req =  $req->withParsedBody($requestBody);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(400, $response->getStatusCode());
    $this->assertSame('{"status":400,"message":"Invalid data provided to update"}', (string)$response->getBody());
  }



  public function testUpdateBooksNotFound() {
    // expected result string
    $resultString = '{"id":"1","name":"hello","year":"1999","author":"Ranu"}';

    // mock the query class & fetchAll functions
    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(false);
    $this->db->method('query')->willReturn($query);
    $this->db->method('exec')->will($this->throwException(new PDOException()));

    // mock the request environment.  (part of slim)
    $env = Environment::mock([
    'REQUEST_METHOD' => 'PUT',
    'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $requestBody = ["name" => "hello", "year" => "1999", "author" => "Ranu"];
    $req =  $req->withParsedBody($requestBody);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(404, $response->getStatusCode());
    $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
  }



  public function testDeletebooksFailed() {
    $query = $this->createMock('mockQuery');
    $this->db->method('exec')->willReturn(false);
    $env = Environment::mock([
    'REQUEST_METHOD' => 'DELETE',
    'REQUEST_URI'    => '/books/1',
    ]);
    $req = Request::createFromEnvironment($env);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    // assert expected status code and body
    $this->assertSame(404, $response->getStatusCode());
    $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
  }




  public function testCreateBooksFailed() {
    // test successful request
    $resultString = '{"id":"4","name":"Heropanthi","year":"2017","author":"Govinda}';

    // mock the query class & fetchAll functions
    $query = $this->createMock('mockQuery');
    $query->method('fetch')->willReturn(json_decode($resultString, true));
    $this->db->method('query')->willReturn($query);
    //mocks where the update failed!!!
    $this->db->method('exec')->will($this->throwException(new PDOException()));

    $env = Environment::mock([
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/books',
    ]);
    $req = Request::createFromEnvironment($env);
    $requestBody = ["name" => "hello", "year" => "1997", "author" => "Ranu"];
    $req =  $req->withParsedBody($requestBody);
    $this->app->getContainer()['request'] = $req;

    // actually run the request through the app.
    $response = $this->app->run(true);
    $this->assertSame(400, $response->getStatusCode());
    $this->assertSame('{"status":400,"message":"Invalid data provided to update"}', (string)$response->getBody());
  }
}
