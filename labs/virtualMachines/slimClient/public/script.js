$(document).ready(function() {
  $("#booksForm").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:8080/firstslim/books",
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8080/slimClient");
      }
    });
  });
  $("#booksEditForm").submit(function(event) {
    alert( "TODO: build submit handler.  See booksForm submit handler for inspiration ")
    var form = $(this);
    var id = $(this).attr("data_id");
    event.preventDefault();
    $.ajax({
      type: "PUT",
      url: "http://localhost:8080/firstslim/books/" + id,
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8080/slimClient");}
});
  });
  $( ".deletebtn" ).click(function() {
  alert( "Are You Sure You Want to Delete" );
  var del_id = $(this).attr("data-id");
  var info = 'id=' + del_id;
  if (confirm("Sure you want to delete this post? This cannot be undone later.")) {
      $.ajax({
          type : "DELETE",
          url : "http://localhost:8080/firstslim/books/" + del_id, //URL to the delete php script
          data : info,
          success : function() {
          }
        });
      };
});
});



    /*$(".delbutton").click(function() {
                var del_id = $(this).attr("id");
                var info = 'id=' + del_id;
                if (confirm("Sure you want to delete this post? This cannot be undone later.")) {
                    $.ajax({
                        type : "DELETE",
                        url : "people/" + del_id, //URL to the delete php script
                        data : info,
                        success : function() {
                        }
        });
        */
