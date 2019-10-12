<?php
/*
BTCTurk PRO Order/Withdraw/Deposit RSS Feed v2.0
Credits: CryptoYakari @CryptoYakari
Reqierments :
BTCTurk PRO Account
PHP Host
Demo Page: https://robostopia.com/btcturk/

This script generates an RSS Feed for your latest BTCTurk Order/Withdraw/Deposit
You can use this RSS feed with IFTTT.com and easily integrate
RSS -> Telegram or RSS->Email
Once your order completed you will get notification.
*/
include("src/Client.php");

$key = 'YOUR_PUBLIC_KEY';
$secret = 'YOUR_PRIVATE_KEY';
$b = new BtcTurkPRO ($key, $secret);

$transactions = json_decode(json_encode($b->UserTransactions()), True);
$transfers_crypto = json_decode(json_encode($b->UserTransfersCrypto()), True);
$transfers_fiat = json_decode(json_encode($b->UserTransfersFiat()), True);
$feeds = array_merge(
    $transfers_crypto["data"],
	$transfers_fiat["data"],
	$transactions["data"]
);

// Sorting array by time (DESC ORDER)
usort($feeds, function($firstItem, $secondItem) {
        $timeStamp1 = $firstItem["timestamp"];
        $timeStamp2 = $secondItem["timestamp"];
        return $timeStamp2 - $timeStamp1;
    });

header('Content-type: application/xml');
echo 
'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
		<title>BTCTurk PRO Transactions RSS Feed v2</title>
		<description>BTCTurk PRO Transactions RSS Feed v2</description>
		<link>https://robostopia.com/btcturk/</link>
		<copyright>CryptoYakari</copyright>
		<atom:link href="https://robostopia.com/btcturk/" rel="self" type="application/rss+xml" />
			';
			
// TÜM İŞLEMLER LİSTELENİYOR

		For ($i = 0; $i < Count($feeds); $i++) {	// CHECK ALL ENTRIES

			$amount = $feeds[$i]["amount"];
			$currency = $feeds[$i]["currencySymbol"];
			$transaction_date = $feeds[$i]["timestamp"]/1000;
			$id = $feeds[$i]["id"];

			If (array_key_exists("balanceType",$feeds[$i])){
				If ($feeds[$i]["balanceType"] === "deposit"){
					$operation = ' yatırma işlemi gerçekleşti.';
					$desc = 'İşleminiz ';}
				Else{
					$operation = ' çekme işlemi gerçekleşti.';
					$desc = 'İşleminiz ';
					}
				
Echo 		'<item>
				<title>BTCTurk '. abs($amount) . ' ' . $currency . $operation .'</title>
				<description>'.$desc.date('d.m.Y H:i:s',$transaction_date).' tarihinde gerçekleşmiştir.</description>
				<link>https://robostopia.com/btcturk/'.$id .'</link>
				<pubDate>'.date(DATE_RSS,$transaction_date).'</pubDate>
				<guid isPermaLink="false">'.$id.'</guid>
			</item>
			';
			}Else{
								
// GERÇEKLEŞEN EMİRLER

				$currency = $feeds[$i]["numeratorSymbol"]."-".$feeds[$i]["denominatorSymbol"];
			
				If ($feeds[$i]["orderType"] == "buy"){
					$buysell = ' alış';
					}
				Else{
					$buysell = ' satış';
					}
			
				$operation = $buysell . ' emriniz gerçekleşti.';
				$desc = 'İşleminiz ' . $feeds[$i]["price"] . ' birim fiyatından ';
			
Echo 		'<item>
				<title>BTCTurk '. abs($amount) . ' ' . $currency . $operation .'</title>
				<description>'.$desc.date('d.m.Y H:i:s',$transaction_date).' tarihinde gerçekleşmiştir.</description>
				<link>https://robostopia.com/btcturk/'.$id .'</link>
				<pubDate>'.date(DATE_RSS,$transaction_date).'</pubDate>
				<guid isPermaLink="false">'.$id.'</guid>
			</item>
			';			
			}

		}	Echo 	
'
		</channel>
</rss>';
?>