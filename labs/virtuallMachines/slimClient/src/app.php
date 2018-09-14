<?php
namespace puspa\apiClient;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

require './vendor/autoload.php';

class App
{
   private $app;

   public function __construct() {

     $app = new \Slim\App(['settings' => $config]);

     $container = $app->getContainer();

     $container['logger'] = function($c) {
         $logger = new \Monolog\Logger('my_logger');
         $file_handler = new \Monolog\Handler\StreamHandler('./logs/app.log');
         $logger->pushHandler($file_handler);
         return $logger;
     };
     $container['renderer'] = new PhpRenderer("./templates");

     function makeApiRequest($path) {
       $ch = curl_init();

       //Set the URL that you want to GET by using the CURLOPT_URL option.
       curl_setopt($ch, CURLOPT_URL, "http://localhost/api/$path");
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

       $response = curl_exec($ch);
       return json_decode($response, true);
     }



     $app->get('/', function (Request $request, Response $response, array $args) {
       $responseRecords = makeApiRequest('books');
       $tableRows = "";
       foreach($responseRecords as $books) {
         $tableRows = $tableRows . "<tr>";
         $tableRows = $tableRows . "<td>".$books["name"]."</td><td>".$books["author"]."</td><td>".$books["year"]."</td>";
         $tableRows = $tableRows . "<td>
         <a href='http://localhost:8080/slimClient/books/".$books["id"]."/view' class='btn btn-primary'>View Details</a>
         <a href='http://localhost:8080/slimClient/books/".$books["id"]."/edit' class='btn btn-secondary'>Edit</a>
         <a data-id='".$books["id"]."' class='btn btn-danger deletebtn'>Delete</a>

         </td>";
         $tableRows = $tableRows . "</tr>";
       }

       $templateVariables = [
           "title" => "Books",
           "tableRows" => $tableRows
       ];
       return $this->renderer->render($response, "/books.html", $templateVariables);
     });

     //endpoint that will allow user to add a new books to the interface. Uses the bookssForm.html for the interface
     $app->get('/books/add', function(Request $request, Response $response) {
       $templateVariables = [
         "type" => "new",
         "title" => "Add new books"
       ];
       return $this->renderer->render($response, "/booksForm.html", $templateVariables);
     });

     $app->get('/books/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $responseRecords = makeApiRequest('books/'.$id);
        $templateVariables = [
          "title" => "View books",
          "book" => $responseRecords
        ];
        return $this->renderer->render($response, "/booksPage.html", $templateVariables);
        return $response;
     });



     $app->get('/books/{id}/edit', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         $responseRecord = makeApiRequest('books/'.$id);
         $templateVariables = [
           "title" => "Edit books",
           "books" => $responseRecord
         ];
         return $this->renderer->render($response, "/booksEdit.html", $templateVariables);

     });

     $this->app = $app;
   }
   /**
    * Get an instance of the application.
    *
    * @return \Slim\App
    */
   public function get()
   {
       return $this->app;
   }
 }
