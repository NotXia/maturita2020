function autocomplete(input, arr) {

   var currentFocus;
   
   input.addEventListener("input", function(e) {
      var val = this.value;

      closeAllLists();

      if (!val) {
          return false;
       }
      currentFocus = -1;

      menu = document.createElement("div");
      menu.setAttribute("id", this.id + "autocomplete-list");
      menu.setAttribute("class", "autocomplete-items");
      this.parentNode.appendChild(menu);

      for (var i=0; i<arr.length; i++) {
         var str_search = arr[i].toUpperCase();
         var val_search = val.toUpperCase();
         if (str_search.includes(val_search)) {
            var pos = str_search.indexOf(val_search);

            row = document.createElement("div");
            row.innerHTML = arr[i].substr(0, pos-1);
            row.innerHTML += "<b>" + arr[i].substr(pos, val_search.length) + "</b>";
            row.innerHTML += arr[i].substr(pos + val_search.length);

            row.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";

            row.addEventListener("click", function(e) {
               input.value = this.getElementsByTagName("input")[0].value;
               closeAllLists();
            });
            menu.appendChild(row);
         }
      }
   });

   input.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x)
         x = x.getElementsByTagName("div");

      if (e.keyCode == 40) { // Giù
         currentFocus++;
         addActive(x);
      } else if (e.keyCode == 38) { // Su
         currentFocus--;
         addActive(x);
      } else if (e.keyCode == 13) { // Enter
         e.preventDefault();
         if (currentFocus > -1) {
            if (x)
               x[currentFocus].click();
         }
      } else if (e.keyCode == 27) { // Esc
         e.preventDefault();
         closeAllLists();
      }
   });

   function addActive(x) {
      if (!x)
         return false;

      for (var i = 0; i < x.length; i++) {
         x[i].classList.remove("autocomplete-active");
      }

      if (currentFocus >= x.length)
         currentFocus = 0;
      if (currentFocus < 0)
         currentFocus = (x.length - 1);

      x[currentFocus].classList.add("autocomplete-active");
   }

   function closeAllLists(elmnt) {
      var x = document.getElementsByClassName("autocomplete-items");
      for (var i=0; i<x.length; i++) {
         if (elmnt != x[i] && elmnt != input) {
            x[i].parentNode.removeChild(x[i]);
         }
      }
   }

}
