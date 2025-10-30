<?php

function get_string_between($string, $start, $end){
    $ini = strpos($string, $start);
    if ($ini === false) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function get_data($url) {
		
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0");

	return curl_exec($ch);

}

// https://www.bilka.dk/produkter/dyson-v12-origin-ledningsfristoevsuger/200280843/

while(true) {

	echo "write URL: ";
	$handle = fopen ("php://stdin","r");
	$url = trim(fgets($handle));
	fclose($handle);

	$name = hash('sha256', $url);

	if (file_exists(dirname(__FILE__)."\input_cache\\".$name.".html")) {
		$data = file_get_contents(dirname(__FILE__)."\input_cache\\".$name.".html");
	} else {
		$data = get_data($url);
		file_put_contents(dirname(__FILE__)."\input_cache\\".$name.".html", $data);
	}

	$filter = get_string_between($data, "<script type=\"application/ld+json\">", "</script>");
	if ($filter == "") {
		echo "Error Getting Data\n\n\n\n";
		continue;
	}

	file_put_contents(dirname(__FILE__)."\output\\".$name.".json", $filter);

	print_r(json_decode($filter, true));
	echo "\n\n\n\n";

}

?>