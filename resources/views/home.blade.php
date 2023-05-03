<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Web Crawler</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <h2>Web Crawler</h2>
            <form name="crawler-post-form" id="crawler-post-form" method="post" action="/crawl">
                @csrf
                <div class="form-group">
                    <input name="url"
                           type="text"
                           placeholder="https://" id="url"
                           class="shadow form-control form-control-lg @error('url') is-invalid @enderror">
                    @error('url')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <input name="pages"
                           type="number"
                           placeholder="pages"
                           id="pages"
                           class="shadow form-control form-control-lg @error('pages') is-invalid @enderror">
                    @error('pages')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button id="start_crawl" type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </body>
</html>
