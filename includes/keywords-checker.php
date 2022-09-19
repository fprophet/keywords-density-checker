

<?php


class KeyWordsChecker extends DatabaseObject{

    public $url;
    public $include_metas = false;
    public $include_titles = false;
    public $include_clickables = false;

    public $page_content;
    public $page_text = "";
    public $page_metas = [];
    public $found_keywords = [];
    public $h_tags = [];
    
    public $clean_words_array;
    public $keywords = [];
    public $one_word = [];
    public $two_words = [];
    public $three_words = [];
    public $four_words = [];



   

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
        $this->url = $url;

        //visit url and get contents
        $this->get_url_contents();
        //load html content from response and find meta tags, h tags , titles and keywords
        $this->load_html();
        //remove elements required by user
        $this->remove_page_elements();
        //remove stop words and white spaces from text
        $this->clean_text();
        // find keyword frequency
        $this->find_kw_freq();
        // find multiple words frequency
        $this->multiple_words_freq();
        // find density, h-tags , titles, descriptions
        $this->calculate_statistics();
    }
    
    public function remove_page_elements(){
        $document = new DOMDocument();
        $document->loadHTML($this->page_content);
        $xpath = new DOMXPath($document);
        $body_path = "//html//body//";
        $head_path = "//html//head//";

        $this->include_metas ? $head_elements[] = "meta" : null;
        $this->include_titles ? $head_elements[] = "title" : null;
        if( $this->include_clickables ) {
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
        $this->page_content = $document->saveHTML();

    }

    public function get_url_contents($user_agent = "cURL", $headers = false){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent );
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false );
        $result = curl_exec($curl);
        curl_close($curl);
        $this->page_content = $result;
    }


    public function load_html(){
        $document = new DOMDocument();
        $document->loadHTML($this->page_content);
        $h_tags = ["h1","h2","h3","h4","h5"];
        $clickable = ["button","a","select"];
        $metas = $document->getElementsByTagName("meta");
        $xpath = new DOMXPath($document);
        $metas = $xpath->query("/html/head/meta");

        //text content from meta tags
        foreach( $metas as $meta){
            $name = strtolower($meta->getAttribute("name"));
            if( $name === "keywords"){
                $this->page_metas["keywords"] = $meta->getAttribute("content");
            }
            if( $name === "description"){
                $this->page_metas["description"] = $meta->getAttribute("content");
            }
        }

        $this->found_keywords = explode(", ", $this->page_metas["keywords"]);

        //text content from titles
        $titles = $xpath->query("/html/head/title");
        foreach( $titles as $title){
                $this->page_metas["title"] = $title->textContent;
        }

        //text content for all h tags
        $path = "//body//";
        foreach( $h_tags as $tag){
            $found_tags = $xpath->query($path . $tag);
            foreach( $found_tags as $found){
                $this->h_tags[] = $found->textContent;
            }
        }

        //remove unused elements
        $to_remove = ["script","style"];
        $path = "//html//";
        foreach( $to_remove as $remove){
            foreach( $xpath->query($path . $remove) as $element){
                $element->parentNode->removeChild($element);
            }
        }
        $this->page_text = $document->textContent;

    }



    public function find_kw_freq(){
        $content = strtolower($this->page_text);
        $total_words = str_word_count($content);
        foreach( $this->found_keywords as $kw){
            $this->keywords[$kw]["freq"] = substr_count($content, strtolower($kw));
        }
        arsort($this->keywords);

    }

    public function clean_text(){  
        $stop_words = "free a able about above abroad according across actually after again against ago ahead all allow allows almost alone along alongside already also although always am amid amidst among amongst an and another any anybody anyhow anyone anything anyway anywhere apart appear are aren't around as aside ask asking at available away awfully back backward be became because become becoming been before begin behind being believe below beside besides best better between beyond both brief but by came can cannot can't caption cause causes certain certainty changes clearly come comes consider contain containing contains could couldn't course currently dare definitely described despite did didn’t different directly do does doesn't doing done don't down during each either else elsewhere end ending enough entirely especially even ever every everybody everyone everything ex exactly example except fairly far farther few fewer five followed following follows for forever former formerly forth forward found from further get gets getting given gives go goes going gone got gotten had hadn't half happens hardly has hasn't have haven't having he he’d he’ll hello help hence her here hereafter hereby herein here's hers herself hes hi him himself his hopefully how however i’d if ignored i’d i’m immediate in indicate inner inside instead into inward is isn't it it’d it’ll its it’s itself I’ve just keep keeps kept know known knows last lately later latter least less lest let let's like liked likely likewise little look looking looks low lower made mainly make makes many may maybe me mean meantime merely might mine minus miss more moreover most mostly mr mrs much must mustn’t my myself name namely near nearly necessary need needs nether never new next no nobody non none noone no-one nor normatly not nothing novel now nowhere obviously or of often oh ok okay old on once only onto opposite or other others otherwise ought oughtn't our ours ourselves out outside over overall own particular past per perhaps placed please plus possible probably provided provides quite rather really recent recently regarding regardless regards right round said same saw say saying says see seeing seem seemed seeming seems seen self selves sensible sent serious seriously several shall shan't she she’d she’ll she's should shouldn't since so some somebody someday somehow someone something sometime sometimes somewhat somewhere soon sorry specified specify still sub such sup sure take taken taking tell tends than thank thanks thanx that that’ll thats that's that’ve the their theirs them themselves then there thereafter thereby there'd therefore therein there’ll there're there's thereupon these they they'd they’ll they're they've thing things think this thorough those though through thru thus till to together too took toward towards tried tries truly try trying un under undoing unless unlike unlikely until unto up upon upwards us use used useful uses using usually value various versus very via viz want wants was wasn't way we we'd welcome well we’ll went were we're weren't we’ve what whatever what what's when whenever where whereas whereby wherein where's wherever whether which whichever while whilst whither who who’d whoever whole who’ll whom whomever who's whose why wilt willing wish with within without wonder won't would wouldn't yes yet you you'd you'll your you're yours yourself yourselves you've";
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

    public function multiple_words_freq(){
        $words_array = ["one_word","two_words","three_words", "four_words"];
        $content = implode(" ", $this->clean_words_array);
        $to_match = '(\w+)';
        $regex_string = '(\w+)';
        foreach( $words_array as $key => $val){
            while(substr_count($regex_string, $to_match) < $key + 1 ){
                $regex_string = $regex_string . " "  . $to_match;
            }
            $offset = 0;
            foreach( $this->clean_words_array as $word_key => $word_val ){
                $offset = $offset + strlen($word_val) + 1;
                preg_match('/' . $regex_string .  '/', $content, $match, 0, $offset);
                if( $match[0] !== null && substr_count($content, $match[0]) !== 0 ){
                    $this->$val[$match[0]]["freq"] = substr_count($content, $match[0]);
                }
            }
        }
        arsort($this->one_word);
        arsort($this->two_words);
        arsort($this->three_words);
        arsort($this->four_words);

        if( count($this->one_word) > 10){
            $this->one_word = array_slice($this->one_word , 0, 10);
        }
        if( count($this->two_words) > 10){
            $this->two_words = array_slice($this->two_words , 0, 10);
        }
        if( count($this->three_words) > 10){
            $this->three_words = array_slice($this->three_words , 0, 10);
        }
        if( count($this->four_words) > 10){
            $this->four_words = array_slice($this->four_words , 0, 10);
        }
            // echo var_dump($this->four_words);

    }

    public function calculate_statistics(){
        $words_array = ["keywords","one_word","two_words","three_words", "four_words"];
        $content = strtolower($this->page_text);
        $total_words = str_word_count($content);
        foreach( $words_array as $words){
            // echo isset()
            foreach( $this->$words as $key => $val){

                //find density
                $this->$words[$key]["density"] = number_format((substr_count($content, $key)/$total_words) * 100,2) . "%";

                //find words in meta tags and title
                foreach( $this->page_metas as $meta_key => $meta_val){
                    if( $meta_key === "keywords"){
                        continue;
                    }else{
                        $this->$words[$key][$meta_key] = (substr_count($meta_val, $key));
                    }
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
