<?php require_once("../includes/keywords-checker.php")?>

<?php
// get POST values;
$_POST["metas"] !== "" ? $metas = true : $metas = false;
$_POST["titles"] !== "" ? $titles = true : $titles = false;
$_POST["clickables"] !== "" ? $clickables = true : $clickables = false;

// create a KeyWordsChecker object with required arguments;
$checker = new KeyWordsChecker($_POST["url"], $metas, $titles, $clickables);

if( $checker->status === "success"){
    // create an array as a response containing the calculated statistics and their words;
    $arr = [ "keywords" => $checker->keywords, "one-word" => $checker->one_word, "two-words" => $checker->two_words,
            "three-words" => $checker->three_words, "four-words" => $checker->four_words,];
}else{
    $arr = ["status" => $checker->status];
}

// encode the array to be read by JavaScript;
echo json_encode($arr);

?>


















