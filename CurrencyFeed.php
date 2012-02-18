<?php

/**

CurrencyFeed (c) 2012 Kasper Souren

    CurrencyFeed is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
   CurrencyFeed is a PHP class for up-to-date currency conversions.
   At construction it does the following:
   
   * Retrieve the data from the API
   * Parse the data
   * Store data in a MySQL table
*/


require_once "config.php";

class CurrencyFeed {
  
  private $debug = 0;

  function __construct($feed, $base_currency = 'EUR') {
    if ($this->debug) {
      print "Using $feed \n";
    }

    $raw_xml = $this->retrieve($feed);
    $this->parseXML($raw_xml);
    $this->store();

    // Base currency of this table
    $this->rates[$base_currency] = 1.0;
  }
  
  private function retrieve($feed) {
    // Retrieving the data from the API
    return file_get_contents($feed);
    // TODO: check if data looks sane, handle connection errors
  }

  private function parseXML($raw_xml) {
    // Parsing the data
    $xml = new SimpleXMLElement($raw_xml);
    $items = $xml->Cube->Cube->Cube;
    $this->rates = array();
    foreach ($items as $entry) {
      $currency = (string) $entry->attributes()->currency;
      $currency = substr($currency, 0, 3);
      $rate = (float) $entry->attributes()->rate;
      $this->rates[$currency] = $rate;
    }
  }

  private function store() {
    /* Store data in a pre-existing MySQL table, 'exchange_rates',
       with 2 fields: currency, rate */

    // CREATE TABLE currency_rates (currency VARCHAR(3) PRIMARY KEY, rate FLOAT);
    
    mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME);

    // Possible addition: DELETE fields, in case a currency has been removed
    foreach ($this->rates as $cur => $rate) {
      $cur = mysql_real_escape_string($cur);
      $sql = 'REPLACE INTO exchange_rates VALUES ("' . $cur . '", ' . $rate . " );";
      mysql_query($sql);
    }
    mysql_close();
  }

  public function convert($s, $dest = 'USD') {
    /* Given an amount of a foreign currency, convert it into the equivalent in US dollars. For example:
       input: 'JPY 5000'
       output: 'USD 65.58'
    */
    
    $matches = array();

    // Matching ZZZ float
    preg_match("/^([A-Z]{3}) ([-+]?([0-9]*\.[0-9]+|[0-9]+))/", $s, $matches);
    $src = $matches[1];
    $amount = $matches[2];
    
    $src_rate = $this->rates[$src];
    $dst_rate = $this->rates[$dest];
    return $amount / $src_rate * $dst_rate;
  }
  
  public function convert_array($arr) {
    /* Given an array of amounts in foreign currencies, return an
       array of US equivalent amounts in the same order. For example…
       input: array( 'JPY 5000', 'CZK 62.5' ) 
       output: array( 'USD 65.58', 'USD 3.27' )
    */
    
    $result = array();
    foreach ($arr as $conversion) {
      $result[] = $this->convert($conversion);
    }
    return $result;
  }

}


// $currency_feed = "http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml";
$currency_feed = "testdata.xml";
$cf = new CurrencyFeed($currency_feed);

print $cf->convert('EUR 100') . "\n";
print $cf->convert('JPY 5000') . "\n";
// TODO: test if output like 'USD 65.58'

var_dump($cf->convert_array(array( 'JPY 5000', 'CZK 62.5' )));
// TODO: test if output like array( 'USD 65.58', 'USD 3.27' )