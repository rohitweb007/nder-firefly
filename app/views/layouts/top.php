<!DOCTYPE html>
<html>
  <head>
    <title>Firefly</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="/css/site.css" rel="stylesheet" media="screen">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  </head>
  <body>
    <?php require_once(__DIR__ . '/../snippets/menu.php'); ?>
    <?php require_once(__DIR__ . '/../snippets/breadcrumbs.php'); ?>

    <?php if (Session::has('error')): ?>
      <div class="row-fluid">
        <div class="span12">
          <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Error:</strong> <?php echo Session::get('error'); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (Session::has('success')): ?>
      <div class="row-fluid">
        <div class="span12">
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>OK</strong> <?php echo Session::get('success'); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (Session::has('warning')): ?>
      <div class="row-fluid">
        <div class="span12">
          <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Warning</strong> <?php echo Session::get('warning'); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>


    <div class="container-fluid">