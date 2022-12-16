let pagination = document.querySelectorAll(".pagination > a");
pagination.forEach((page) => {
  page.addEventListener("click", ajaxCall);
});

function ajaxCall() {
  var page = this.dataset.page;
  var per_page = document.getElementById("per_page").value;
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      // Replace the div with articles with the response from ajax call
      // Remove log
      console.log(xhttp.response);
    }
  };
  xhttp.open(
    "GET",
    frontendajax.ajaxurl +
      "?" +
      [
        "action=displayPostsAjax",
        "args[per_page]=" + per_page,
        "args[page]=" + page,
        "taxonomies",
      ].join("&"),
    true
  );
  xhttp.send();
}
