<?php

/*
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(255),
    gtin13 VARCHAR(255),
    name VARCHAR(255),
    brand VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    currency VARCHAR(10),
    url TEXT,
    images JSON
);
*/

while(true) {
	$host = "localhost";
	$user = "test";
	$pass = "test";
	$dbname = "storejson";

	$conn = new mysqli($host, $user, $pass, $dbname);
	if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
	}
	
	$files = glob(dirname(__FILE__)."\output\\"."*.json");
	sleep(2);
	foreach ($files as $file) {
		echo "Reading file: $file\n";

    $jsonData = file_get_contents($file);
    $data = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Invalid JSON in $file\n";
				unlink($file);
        continue;
    }

    $stmt = $conn->prepare("
        INSERT INTO products 
        (sku, gtin13, name, brand, description, price, currency, url, images)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $sku = $data['sku'] ?? null;
    $gtin13 = isset($data['gtin13']) ? $data['gtin13'] : $data['gtin'];
    $name = $data['name'] ?? null;
		if (isset($data['brand']['name'])) {
			$data['brand'] = $data['brand']['name'];
		}
    $brand = $data['brand'] ?? null;
    $description = $data['description'] ?? null;
		
		if (isset($data['offers'][0])) {
			$data['offers'] = $data['offers'][0];
		}
		
    $price = $data['offers']['price'] ?? 0;
    $currency = $data['offers']['priceCurrency'] ?? null;
    $url = $data['offers']['url'] ?? null;
    $images = isset($data['image']) ? json_encode($data['image']) : null;

    $stmt->bind_param("sssssdsss", $sku, $gtin13, $name, $brand, $description, $price, $currency, $url, $images);
    $stmt->execute();

    echo "Inserted: $name\n";
		
		unlink($file);
	}
	
	$conn->close();
}

?>