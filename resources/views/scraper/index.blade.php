<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <title>SCRAPING</title>
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-md-6 offset-md-3 mt-5 wrapper">
      @foreach($data as $key =>$value)
      <div class="card text-center mt-4">
        <h5 class="card-header bg-success"> {{$key}}</h5>
        <div class="card-body">
          <p class="card-text">
            {{$value}}
          </p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
</body>
</html>
