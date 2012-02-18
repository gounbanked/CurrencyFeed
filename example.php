<?php

require_once "CurrencyFeed.php";

// e.g. https://www.rates.com/cgi-bin/xml?currency=all
$currency_feed = "testdata.xml";
$cf = new CurrencyFeed($currency_feed);

print $cf->convert('EUR 100') . "\n";
print $cf->convert('JPY 5000') . "\n";
print $cf->convert('USD 100') . "\n";

var_dump($cf->convert_array(array( 'JPY 5000', 'CZK 62.5' )));
