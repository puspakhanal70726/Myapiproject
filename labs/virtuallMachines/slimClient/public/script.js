$(document).ready(function() {
  $("#booksForm").submit(function(event) {
    var form = $(this);
    console.log(form.serialize()),
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:8080/api/books",
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8080/slimClient/");
      }
    });
  });


  $( ".deletebtn" ).click(function() {
    if (window.confirm("Do you want to delete this books?")) {
      $.ajax({
        type: "DELETE",
        url: "http://localhost:8080/api/books/" + $(this).attr("data-id"),
        success: function(data) {
          window.location.reload();
        }
      });
    }
  });



  $("#booksEdit").submit(function(event) {
    var form = $(this);
    console.log($(this).attr("data-id"))
    event.preventDefault();
    $.ajax({
      type: "PUT",
      url: "http://localhost:8080/api/books/" + $(this).attr("data-id"),
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8080/slimClient");
      }
    });
  });
});
