<?php
function deal($deck, $players, $handsize = null) {
	if (!is_array($deck)) {
		$deck = explode(",", $deck);
	}
	if ($handsize == null) {
		$handsize = floor(count($deck) / $players);
	}
	$out = array();
	for ($x=1 ; $x<=$players ; $x++) {
		$player = "p" . $x;
		$out[$player] = array();
	}
	foreach ($out as $key => $hand) {
		for ($y=0 ; $y<$handsize ; $y++) {
			$card = array_pop($deck);
			$out[$key][] = $card;
		}
	}
	if (!empty($deck)) {
		foreach ($out as $key => $hand) {
			if (empty($deck)) {
				break 1;
			}
			$card = array_pop($deck);
			$out[$key][] = $card;
		}
	}
	$dealt = array($deck, $out);
	return $dealt;
}

function sortCards($hand, $order = "aces,numbers,jacks,queens,kings", $suits = "c,d,h,s", $append = null) {
	if (!is_array($hand)) {
		$hand = explode(",", $hand);
	}
	$suits = explode(",", $suits);
	$suits = implode("|", $suits);
	$numbers = array();
	$jacks = array();
	$queens = array();
	$kings = array();
	$aces = array();
	foreach ($hand as $key => $card) {
		if (preg_match('#[\d]#', $card) and preg_match("/$suits/", $card)) {
			$numbers[] = $card;
		}
		elseif (strpos($card, "j") !== false and preg_match("/$suits/", $card)) {
			$jacks[] = $card;
		}
		elseif (strpos($card, "q") !== false and preg_match("/$suits/", $card)) {
			$queens[] = $card;
		}
		elseif (strpos($card, "k") !== false and preg_match("/$suits/", $card)) {
			$kings[] = $card;
		}
		elseif (strpos($card, "a") !== false and preg_match("/$suits/", $card)) {
			$aces[] = $card;
		}
	}
	natsort($numbers);
	natsort($jacks);
	natsort($queens);
	natsort($kings);
	natsort($aces);
	$order = explode(",", $order);
	$collection = array("aces" => $aces, "numbers" => $numbers, "jacks" => $jacks, "queens" => $queens, "kings" => $kings);
	$sort = array();
	foreach ($order as $type) {
		$sort = array_merge($sort, $collection[$type]);
	}
	if ($append != null) {
		$append = explode(",", $append);
		$end = array();
		foreach ($append as $card) {
			$key = array_search($card, $sort);
			if (is_numeric($key)) {
				unset($sort[$key]);
				$end[] = $card;
			}
		}
		natsort($end);
		$sort = array_merge($sort, $end);
	}
	return $sort;
}

function aiPlay($hand, $rank) {
	$deck = "2h,3h,4h,5h,6h,7h,8h,9h,10h,jh,qh,kh,ah,2c,3c,4c,5c,6c,7c,8c,9c,10c,jc,qc,kc,ac,2s,3s,4s,5s,6s,7s,8s,9s,10s,js,qs,ks,as,2d,3d,4d,5d,6d,7d,8d,9d,10d,jd,qd,kd,ad";
	$deck = explode(",", $deck);
	$copy = $hand;
	$play = array();
	foreach ($copy as $key => $card) {
		if (strpos($card, $rank) !== false) {
			$play[] = $card;
			unset($copy[$key]);
		}
	}
	if (empty($play)) {
		$rand = array(1,1,1,1,2,2,3,4);
		$numb = $rand[array_rand($rand, 1)];
		if ($numb > count($copy)) {
			$numb = count($copy);
		}
		$c = 0;
		while ($c<$numb) {
			$key = array_rand($copy, 1);
			if (array_key_exists($key, $copy)) {
				$play[] = $copy[$key];
				unset($copy[$key]);
				$c++;
			}
		}
		$rand = array(1,1,1,1,1,1,1,2);
		$rand = $rand[array_rand($rand, 1)];
		if ($rand == 2 and count($play) < 4) {
			$key = array_rand($hand, 1);
			if (array_key_exists($key, $copy)) {
				$play[] = $copy[$key];
				unset($copy[$key]);
			}
		}
	}
	$rand = array(1,1,1,1,1,1,1,1,1,1,1,1,1,2);
	$rand = $rand[array_rand($rand, 1)];
	if ($rand == 2 and count($play) < 4) {
		$key = array_rand($hand, 1);
		if (array_key_exists($key, $copy)) {
			$play[] = $copy[$key];
			unset($copy[$key]);
		}
	}
	if (empty($play)) {
		$play[] = current($copy);
	}
	foreach ($play as $key => $card) {
		if (!in_array($card, $deck)) {
			unset($play[$key]);
		}
	}
	$play = implode(",", $play);
	return $play;
}

function ai_detectCheat($hand, $prev, $rank, $turn, $prevturn, $prevhand) {
	if (!is_array($hand)) {
		$placed = explode(",", $hand);
	}
	if (!is_array($prevhand)) {
		$placed = explode(",", $prevhand);
	}
	$placed = count($prev);
	$my_rank_count = 0;
	$cheat = false;
	if ($turn != $prevturn) {
		foreach ($hand as $card) {
			if (strpos($card, $rank) !== false) {
				$my_rank_count++;
			}
		}
		$sum = $placed + $my_rank_count;
		if ($sum > 4) {
			$cheat = "p" . $turn;
		}
		elseif ($my_rank_count == 4) {
			$cheat = "p" . $turn;
		}
		elseif (count($prevhand) == 0) {
			$random = array(1,1,1,2);
			$get = $random[array_rand($random, 1)];
			if ($get == 1) {
				$cheat = "p" . $turn;
			}
		}
		if (count($prevhand) == 2) {
			$random = array(1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2);
			$get = $random[array_rand($random, 1)];
			if ($get == 1) {
				$cheat = "p" . $turn;
			}
		}
		elseif ($my_rank_count == 3) {
			$random = array(1,1,1,2);
			$get = $random[array_rand($random, 1)];
			if ($get == 1) {
				$cheat = "p" . $turn;
			}
		}
	}
	return $cheat;
}

function showCards($hand) {
	foreach ($hand as $card) {
		$file = $card . ".jpg";
		echo "<img src='cheatresources/" . $file . "' height='100' width='90'>  ";
	}
	echo "</div>";
}
?>