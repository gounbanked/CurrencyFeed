<?php

require_once "CurrencyFeed.php";

// $currency_feed = "http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml";
$currency_feed = "testdata.xml";
$cf = new CurrencyFeed($currency_feed);

print $cf->convert('EUR 100') . "\n";
print $cf->convert('JPY 5000') . "\n";
// TODO: test if output like 'USD 65.58'

var_dump($cf->convert_array(array( 'JPY 5000', 'CZK 62.5' )));
// TODO: test if output like array( 'USD 65.58', 'USD 3.27' )