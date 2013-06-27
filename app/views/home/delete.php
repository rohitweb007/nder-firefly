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
            If you click delete, you'll be logged out AND all your data will
            be permanently deleted.
          </p>
          <?php echo Form::open(); ?>
            <?php echo Form::submit('Delete',array('class' => 'btn btn-danger btn-large'));?>
          <?php echo Form::close(); ?>
        </div>
      </div>
    </div>
  </body>
</html>

