<!DOCTYPE html>
<html>
  <head>
    <title>Firefly</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="/css/site.css" rel="stylesheet" media="screen">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  </head>
  <body>
    @include('snippets.menu')
    <div class="container-fluid">
      @yield('content')
    </div>
  </body>

  <script src="/bootstrap/js/bootstrap.min.js"></script>
  <script src="/js/site.js"></script>

</html>