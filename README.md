PHP Class Keywords Density Checker
Used to find keywords density and most frequent words and combination of words in a webpage. It requires 4 arguments to be created: url, include_metas, include_titles, include click-ables which are being provided in index.php by a form.
The class uses the PHP curl library to request the page and PHP built-in classes DOMDocument and DOMXPath to load the page HTML and find key components such as meta tags titles and H tags.

The class object is created in find-density.php witch is fetched by a JavaScript async function and posts into it the url of the page inserted by user and whether or not the calculations should include specific page tags.
The results are encoded as JSON and echo-ed into find-density.php. The JavaScript fetch function is used to retrieve the results.
The density formula used is: (freq/total_words)*100.
A list of stop words is provided in the class to avoid including them into calculations.

In case of missing content, page not being found or internal server error a message will be displayed indicating the problem.

The tables are created and populated by JavaScript and certain classes of Bootstrap 5 are used for a nice and quick page design.
