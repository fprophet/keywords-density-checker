
<?php

class KeyWordsChecker {

    public $curl;
    public $url;
    public $include_metas = false;
    public $include_titles = false;
    public $include_clickables = false;
    // property $status contains a string with http errors;
    public $status = "";
    // property $page_content contains the page text and HTML tags;
    public $page_content;
    //property $page_text contains page text without HTML tags;
    public $page_text = "";
    // class properties that contain text content of metas, titkes, keywords and h-tags
    public $page_metas = [];
    public $page_titles = [];
    public $found_keywords = [];
    public $h_tags = [];
    
    //property $clean_word_array contains an array of cleaned words from page text;
    public $clean_words_array;
    // class properties that contain keywords and multiple words combinations and their statistics
    public $keywords = [];
    public $one_word = [];
    public $two_words = [];
    public $three_words = [];
    public $four_words = [];

    // @Params:{$url = url to visit; $metas = wether include meta tags or not; $titles = wether include titles or not};
    //           $clickables = wether include clickable elements;
    function __construct($url, $metas = false, $titles = false, $clickables = false){
        if( $metas ){
            $this->include_metas = true;
        }
        if( $titles ){
            $this->include_titles = true;
        }
        if( $clickables ){
            $this->include_clickables = true;
        }
        $this->check_https($url);
        //visit url and get contents
        if( $this->get_url_contents() ) {
            //load html content from response
            if ( $this->load_html() !== false ){
                 //remove elements that are not included by user
                $this->remove_page_elements();
                //remove stop words and white spaces from text
                $this->clean_text();
                // find keywords sfrequency
                $this->find_kw_freq();
                // find combination of words frequency
                $this->multiple_words_freq();
                // find words combination density, appearances in description, titles and H-tags
                $this->calculate_statistics();
            }
        }
    }
    
    // method that will populate class property $page_content if the curl requests has succedded and the results are not empty;
    public function get_url_contents(){
        $result = $this->curl_request($this->url);
        if ( $this->set_response_status(curl_getinfo($this->curl), $result) === false){
            return false;
        }else{
            $this->page_content = $result;
            return true;
        }
    }   

    //  method that requests the url through cURL;
    // @param { $url = strign containing url to visit};
    public function curl_request($url){
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $url );
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($this->curl, CURLOPT_USERAGENT, "cURL" );
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true );
        $result = curl_exec($this->curl);
        curl_close($this->curl);
        return $result;
    }

    // method that sets class property $status based on the curl request response
    // @params { $status = array of curl information; $result = result of curl request }
    public function set_response_status($status, $result){
        $status_code = $status["http_code"];
        if( $status_code === 0){
            $this->status = "Could not connect to host!";
            return false;
        }
        if( $status_code >= 200 && $status_code < 400){
            if( $result !== ""){
                $this->status = "success";
                return true;
            }else{
                $this->status = "Requested page is empty!";
                return false;
            }
        }
        if( $status_code >= 400 && $status_code < 500){
            $this->status = "Requested page not found!";
            return false;
        }
        if( $status_code >= 500 ){
            $this->status = "The request has encountered a server error!";
            return false;
        }
    }

    // check if the inserted url contains  https or http protocol and adds "https://" to url if they are missing
    // @param {$url = string containing url}
    public function check_https($url){
        if( strpos( $url, "https://" ) === false && strpos( $url, "http://" ) === false ){
            $this->url = "https://" . $url;
        }else{
            $this->url = $url;
        }
    }

    // method that loads content as HTML;
    // uses PHP built-in class DOMDocument to load the page contents and DOMXPath to find specific tags
    // saves contents from keywords, meta tags, titles and H tags in class properties;
    public function load_html(){
        if( $this->page_content == "" ){
            return false;
        }
        $document = new DOMDocument();
        $document->loadHTML($this->page_content);
        
        //arrays used in Xpath to find html tags
        $h_tags = ["h1","h2","h3","h4","h5"];
        $clickable = ["button","a","select"];

        $xpath = new DOMXPath($document);
        $metas = $xpath->query("/html/head/meta");

        // save the text content from meta tags
        foreach( $metas as $meta){
            $name = strtolower($meta->getAttribute("name"));
            if( $name === "keywords"){
                $this->page_metas["keywords"] = $meta->getAttribute("content");
            }
            if( $name === "description"){
                $this->page_metas["description"] = $meta->getAttribute("content");
            }
        }

        // save keywords found in page content
        if ( $this->page_metas["keywords"] !== null ){
            $this->found_keywords = explode(", ", $this->page_metas["keywords"]);
        }

        // save the text content from titles
        $titles = $xpath->query("/html/head/title");
        foreach( $titles as $title){
                $this->page_titles[] = $title->textContent;
        }
        // save the text content for all h tags
        $path = "//body//";
        foreach( $h_tags as $tag){
            $found_tags = $xpath->query($path . $tag);
            foreach( $found_tags as $found){
                $this->h_tags[] = $found->textContent;
            }
        }
        // remove script and style tags from page contents
        $to_remove = ["script","style"];
        $path = "//html//";
        foreach( $to_remove as $remove){
            foreach( $xpath->query($path . $remove) as $element){
                $element->parentNode->removeChild($element);
            }
        }
        // after removing the above tagse save the new HTML content to class property "page_content"
        $this->page_content = $document->saveHTML();

    }

    // method that removes elements from the page that are not included by users;
    // uses DOMDocument and DOMXpath built-in classes;
    public function remove_page_elements(){
        $document = new DOMDocument();
        $document->loadHTML($this->page_content);
        $xpath = new DOMXPath($document);
        $body_path = "//html//body//";
        $head_path = "//html//head//";

        // arrays: head_elements, body_elements are populated if the users don't include specific tags
        $this->include_metas ?  null : $head_elements[] = "meta";
        $this->include_titles ?  null : $head_elements[] = "title";
        if( !$this->include_clickables ) {
            $body_elements[] = "button";
            $body_elements[] = "a";
            $body_elements[] = "select";
            $body_elements[] = "input";
        }

        if( !empty( $head_elements ) ){
            foreach( $head_elements as $element ){
                $found = $xpath->query( $head_path . $element);
                foreach( $found as $to_remove ){
                    $to_remove->parentNode->removeChild($to_remove);
                }
            }
        }

        if( !empty( $body_elements ) ){
            foreach( $body_elements as $element ){
                $found = $xpath->query( $body_path . $element);
                foreach( $found as $to_remove ){
                    $to_remove->parentNode->removeChild($to_remove);
                }
            }
        }

        // save the remaining content to class property "page_content"
        $this->page_content = $document->saveHTML();

        // save the page text to class property "page_text"
        $this->page_text = $document->textContent;
    }

    // method that formats the page text saved in class property page_text;
    // an array of stop words is declared and is used to remove them from the page text;
    // removes horizontal and vertical and special characters;
    // remaining text is exploded into an array saved in class property $clean_words_array;
    public function clean_text(){  
        $stop_words = "a able about above abroad according across actually after again against ago ahead all allow allows almost alone along alongside already also although always am amid amidst among amongst an and another any anybody anyhow anyone anything anyway anywhere apart appear are aren't around as aside ask asking at available away awfully back backward be became because become becoming been before begin behind being believe below beside besides best better between beyond both brief but by came can cannot can't caption cause causes certain certainty changes clearly come comes consider contain containing contains could couldn't course currently dare definitely described despite did didn’t different directly do does doesn't doing done don't down during each either else elsewhere end ending enough entirely especially even ever every everybody everyone everything ex exactly example except fairly far farther few fewer five followed following follows for forever former formerly forth forward found from further get gets getting given gives go goes going gone got gotten had hadn't half happens hardly has hasn't have haven't having he he’d he’ll hello help hence her here hereafter hereby herein here's hers herself hes hi him himself his hopefully how however i’d if ignored i’d i’m immediate in indicate inner inside instead into inward is isn't it it’d it’ll its it’s itself I’ve just keep keeps kept know known knows last lately later latter least less lest let let's like liked likely likewise little look looking looks low lower made mainly make makes many may maybe me mean meantime merely might mine minus miss more moreover most mostly mr mrs much must mustn’t my myself name namely near nearly necessary need needs nether never new next no nobody non none noone no-one nor normatly not nothing novel now nowhere obviously or of often oh ok okay old on once only onto opposite or other others otherwise ought oughtn't our ours ourselves out outside over overall own particular past per perhaps placed please plus possible probably provided provides quite rather really recent recently regarding regardless regards right round said same saw say saying says see seeing seem seemed seeming seems seen self selves sensible sent serious seriously several shall shan't she she’d she’ll she's should shouldn't since so some somebody someday somehow someone something sometime sometimes somewhat somewhere soon sorry specified specify still sub such sup sure take taken taking tell tends than thank thanks thanx that that’ll thats that's that’ve the their theirs them themselves then there thereafter thereby there'd therefore therein there’ll there're there's thereupon these they they'd they’ll they're they've thing things think this thorough those though through thru thus till to together too took toward towards tried tries truly try trying un under undoing unless unlike unlikely until unto up upon upwards us use used useful uses using usually value various versus very via viz want wants was wasn't way we we'd welcome well we’ll went were we're weren't we’ve what whatever what what's when whenever where whereas whereby wherein where's wherever whether which whichever while whilst whither who who’d whoever whole who’ll whom whomever who's whose why wilt willing wish with within without wonder won't would wouldn't yes yet you you'd you'll your you're yours yourself yourselves you've";
        $stop_words = explode(" ", $stop_words);
        $content = strtolower($this->page_text);
        $content = str_replace(",","", $content );
        $content = preg_replace('/\v+/', ' ', $content); 
        $content = preg_replace('/\s+/', ' ', $content); 
        $content = preg_replace('/[^A-Za-z\- ]/', '', $content);
        $words_array = explode(" ", $content);
        $new_array = [];
        foreach($words_array as $word){
            if( !in_array($word,$stop_words) && !empty($word)){
                $new_array[] = $word;
            }
        }
        $this->clean_words_array = $new_array;
    }

    // finds the frequency of each page keyword;
    public function find_kw_freq(){
        if ( count($this->found_keywords) < 1 ){
            return false;
        }
        $content = strtolower($this->page_text);
        $total_words = str_word_count($content);
        foreach( $this->found_keywords as $kw){
            $this->keywords[$kw]["freq"] = substr_count($content, strtolower($kw));
        }
        arsort($this->keywords);

    }

    // finds the frequency of each combination of words;
    // only first 10 records of each array are saved;
    public function multiple_words_freq(){
        // array with class property names;
        $words_array = ["one_word","two_words","three_words", "four_words"];
        $content = implode(" ", $this->clean_words_array);
        $to_match = '(\w+)';
        // regex string used to find word combinations of 1, 2, 3 and 4 consecutive words;
        $regex_string = '(\w+)';
        //loop trough each property name
        foreach( $words_array as $key => $val){
            // add regex of a single word to regex string in respect to current number of words combination
            while(substr_count($regex_string, $to_match) < $key + 1 ){
                $regex_string = $regex_string . " "  . $to_match;
            }
            $offset = 0;

            // loop trough class property "clean_words_array" and use preg_match() function to find combination of words
            foreach( $this->clean_words_array as $word_key => $word_val ){
                //offest used to skip words that have been looped trough
                $offset = $offset + strlen($word_val) + 1;
                preg_match('/' . $regex_string .  '/', $content, $match, 0, $offset);
                if( $match[0] !== null && substr_count($content, $match[0]) !== 0 ){
                    $this->$val[$match[0]]["freq"] = substr_count($content, $match[0]);
                }
            }

            //sort array by values and select first 10 items;
            arsort($this->$val);
            if( count($this->$val) > 10){
                $this->$val = array_slice($this->$val , 0, 10);
            }
        }
    }

    // method that calculates the density, appearances in meta tags, titles, and H tags of keywords and combination of words;
    // density is calculated by the general method: density = (word_frequency / total_page_words) * 100;
    // if specific tags are not included by users their values will be replaced by "-";
    public function calculate_statistics(){
        $words_array = ["keywords","one_word","two_words","three_words", "four_words"];
        $content = strtolower($this->page_text);
        $total_words = str_word_count($content);
        foreach( $words_array as $words){
            foreach( $this->$words as $key => $val){

                //find density with 2 decimals
                $this->$words[$key]["density"] = number_format(($this->$words[$key]["freq"]/$total_words) * 100, 2) . "%";

                //find words in titles
                if( $this->include_titles ){
                    foreach( $this->page_titles as $title_key => $title_val){
                        $this->$words[$key]["titles"] = (substr_count(strtolower($title_val), $key));
                    }
                }else{
                    $this->$words[$key]["titles"] = "-";
                }

                //find words in meta tags
                if( $this->include_metas ){
                    foreach( $this->page_metas as $meta_key => $meta_val){
                        if( $meta_key === "keywords"){
                            continue;
                        }else{
                            $this->$words[$key][$meta_key] = (substr_count(strtolower($meta_val), $key));
                        }
                    }
                }else{
                    $this->$words[$key]["metas"] = "-";
                }
               
                //find words in H-tags
                $this->$words[$key]["h"] = 0;
                foreach( $this->h_tags as $h_key => $h_val){
                    $this->$words[$key]["h"] =  $this->$words[$key]["h"] + substr_count(strtolower($h_val), $key);
                }
            }
           
        }
    }
    
}

?>
