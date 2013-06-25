<!DOCTYPE html>
<html>
  <head>
    <title>Firefly</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="span12"><h1>Firefly</h1></div>
      </div>
    </div>
    <div class="container">
      <div class="row">
        <div class="span8">
          <p>
            Firefly is a personal finances app anybody can use. It allows you
            to track your personal accounts, save transactions, set saving targets
            and tries to make some neat graphs to show you how your doing.
          </p>
          <p>
            You can find an example screenshot here (todo).
          </p>
          <p>
            Firefly is a small personal project and therefor it has a few properties that
            might make you want to turn away from it:
          </p>
          <ul>
            <li>It's completely focussed on the way I handle my money. There's <a href="/concept">a conceptual explanation here</a>.
            If you do things differently, it's too bad.</li>
            <li>Although Firefly encrypts a lot of data it doesn't encrypt everything and it isn't perfect. I
              solemnly swear I won't take a peak but in theory I can look at whatever you put in.</li>
            <li>If I decide to change anything and everything breaks it's bad luck.</li>
          </ul>
          <p>
            If you want to use this app comfortably: log in, enter some bogus data and see what happens.
          </p>
          <p>
            <strong>
              {{HTML::Link($url,'Log in or register here')}}</strong>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>

