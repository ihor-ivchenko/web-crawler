<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Web Crawler</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <a href="{{ url('/') }}" class="btn btn-xs btn-info pull-right">Back To Main</a>
            <h2>Crawler Result</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Link</th>
                        <th scope="col">Page Title</th>
                        <th scope="col">HTTP Status Code</th>
                        <th scope="col">Link Type</th>
                        <th scope="col">Loading Time</th>
                        <th scope="col">Words Unique Qty</th>
                        <th scope="col">Words Total</th>
                        <th scope="col">Images Qty</th>
                        <th scope="col">Links Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listing['pagesData'] ?? [] as $page)
                        <tr>
                            <td>{{$page['href']}}</td>
                            <td>{{$page['page_title']}}</td>
                            <td>{{$page['code']}}</td>
                            <td>{{$page['link_type']}}</td>
                            <td>{{$page['loading_time']}}</td>
                            <td>{{$page['word_qty']}}</td>
                            <td>{{$page['total_words']}}</td>
                            <td>{{$page['image_qty']}}</td>
                            <td>{{$page['links_qty']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="container">
            <h2>Additional Result</h2>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th scope="col">Number of pages crawled</th>
                    <th scope="col">Number of a unique images</th>
                    <th scope="col">Number of unique internal links</th>
                    <th scope="col">Number of unique external links</th>
                    <th scope="col">Average page load in seconds</th>
                    <th scope="col">Average word count</th>
                    <th scope="col">Average title length</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($listing['generalData'] ?? [] as $value)
                            <td>{{$value}}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>



