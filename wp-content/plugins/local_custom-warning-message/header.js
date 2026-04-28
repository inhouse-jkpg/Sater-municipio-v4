window.addEventListener("load", function(event) {
  var warningMsg = document.getElementById('warning-message');
  if(warningMsg != undefined){
    var transparentHeader = document.getElementsByClassName('navbar-transparent');
    transparentHeader.item(0).className += ' transparent-header-with-warningMsg ';
  }
});