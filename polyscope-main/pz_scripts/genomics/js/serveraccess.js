//
// Author: Sebastian Schmittner (stp.schmittner@gmail.com)
// Date: 2015.10.11 15:04:37 (+02:00)
// LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
// LastDate: 2015.10.12 15:10:05 (+02:00)
// Version: 0.0.6
//

// 
// Server access functions
// 

function serverRequest(url, content, fnStateChange, context)
{
  var request = null;

  if (window.XMLHttpRequest)
  {
    request = new XMLHttpRequest();
    request.open('post', url, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.send(content);
    request.onreadystatechange = fnStateChange;
    request.context = context;
  }

  return request;
}


