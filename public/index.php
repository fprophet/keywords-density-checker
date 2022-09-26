<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preload" href="/bootstrap/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css"></noscript>
    <title>Keywords Chekcer</title>
</head>
<body>

<div class="container">
    <div class="main-content text-center mb-5">
        <h1 class="h1 mb-0  page_heading">Keywords Density Checker For Websites</h1>
        <span class='fs-6'>Calculate the keyword density and the most used words on your webpage!</span>
        <form action="" class="mt-3 ">
            <div class="input-group mb-3 ">
                <input type="text" name="url" id="url" class="w-75 text-center m-auto" placeholder="Insert the url to your webpage">
            </div>
            <div class="input-group mb-3">
                <button type="button"  class="m-auto btn-primary btn" onclick="density()">Calculate Density</button>
            </div>
            <div class="input-group mb-3 m-auto  justify-content-center ">
                <label for="metas">Metas</label>
                <input type="checkbox" name="metas" value="" class="mx-4">
                <label for="titles">Titles</label>
                <input type="checkbox" name="titles" value="" class="mx-4">
                <label for="clickables">Clickables</label>
                <input type="checkbox" name="clickables" value="" class="mx-4">
            </div>
            <div id="processing" hidden class="text-center">
                <p class="d-inline">Processing your request</p>
                <p class="d-inline point1">.</p>
                <p class="d-inline point2">.</p>
                <p class="d-inline point3">.</p>
            </div>
        </form>
        <div id="results" hidden>
            
        </div>
    </div>
</div>
    <script   src="/javascript/main.js"></script>
    <script >check_box()</script>



</body>
</html>