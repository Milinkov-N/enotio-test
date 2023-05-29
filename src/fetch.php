<?php
require_once "db.php";

$db = new Db('enotio');

$db->addTimestamp();

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, "http://www.cbr.ru/scripts/XML_daily.asp?date_req=" . date("d/m/Y"));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$res = curl_exec($curl);
curl_close($curl);

$xml = new SimpleXMLElement($res);

for ($xml->rewind(); $xml->valid(); $xml->next()) {
    $charCode = null;
    $nominal  = null;
    $name  = null;
    $value = null; 

    foreach($xml->getChildren() as $key => $data) {
        switch ($key) {
            case "CharCode":
                $charCode = $data;
                break;
            case "Nominal":
                $nominal = $data;
                break;
            case "Name":
                $name = $data;
                break;
            case "Value":
                $value = $data;
                break;
        }
    }

    $db->addCurrency($charCode, $nominal, $name, $value);
}

$db->close();
unset($db);
?>