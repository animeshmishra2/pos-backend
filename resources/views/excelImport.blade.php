<!DOCTYPE html>
<html>

<head>
    <title> Import Excel </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
</head>

<body>
    <h6> Import Excel data
    </h6>
    <div class="container">
        <div class="card bg-light mt-3">
            <div class="card-header">
                Import Excel data
            </div>
            <div class="card-body">
                <form action="{{route('excelImport')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" class="form-control">
                    <br>
                    <button class="btn btn-success">
                        Import User Data
                    </button>
                </form>
                <?php  echo phpinfo(); ?>
            </div>
        </div>
    </div>

</body>

</html>