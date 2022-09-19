<?php require_once("../includes/initialize.php")?>
<?php require_once("../includes/keywords-checker.php")?>

<?php
$_POST["metas"] !== "" ? $metas = true : $metas = false;
$_POST["titles"] !== "" ? $titles = true : $titles = false;
$_POST["clickables"] !== "" ? $clickables = true : $clickables = false;

$checker = new KeyWordsChecker($_POST["url"], $metas, $titles, $clickables);

$arr = [ "keywords" => $checker->keywords, "one-word" => $checker->one_word, "two-words" => $checker->two_words,
            "three-words" => $checker->three_words, "four-words" => $checker->four_words,];
// echo var_dump($checker->keywords);
echo json_encode($arr);

?>


















