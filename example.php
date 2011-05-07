<?php

include("nekomata.php");

$xml = nekomata::_new();

// common usage, create/append new elements inside current active node.
// nekomata::_up() will set parent node as the active node.
$xml
	->group(array("name" => "Subterranean Animism"))
		->member("Komeiji Satori", array("title" => "The Girl Feared by Evil Spirits", "type" => "Satori"))
		->member("Komeiji Koishi", array("title" => "Closed Eyes of Love", "type" => "Satori"))
		->member("Reiuji Utsuho", array("title" => "Scorching, Troublesome Divine Flame", "type" => "Yatagarasu"))
		->member("Kaenbyou Rin", array("title" => "Traffic Accident of Hell", "type" => "Kasha"))
		->member("Hoshiguma Yuugi", array("title" => "Rumored Unnatural Phenomenon", "type" => "Oni"))
		->member("Kurodani Yamame", array("title" => "The Shining Net in the Dark Cave", "type" => "Earth Spider"))
		->member("Kisume", array("title" => "The Fearsome Well Spirit", "type" => "Tsurube Otoshi"))
		->_up()
	->group(array("name" => "Embodiment of Scarlet Devil"))
		->member("Remilia Scarlet", array("title" => "Eternally Young Scarlet Moon", "type" => "Vampire"))
		->member("Flandre Scarlet", array("title" => "Sister of the Devil", "type" => "Vampire"));


// entry below will be put inside the first group
$xml->group->member("Mizuhashi Parsee", array("title" => "Jealousy Beneath the Crust of the Earth", "type" => "Bridge Princess"));

// entries below will be put inside the second group
$xml->group[1]
	->member("Izayoi Sakuya", array("title" => "Maid of the Scarlet Devil Mansion", "type" => "Human"))
	->member("Patchouli Knowledge", array("title" => "The Unmoving Great Library", "type" => "Magician"))
	->member("China", array("title" => "Chinese Girl", "type" => "Youkai"))
	->member("Koakuma", array("title" => "Little Devil", "type" => "Devil"));

// modify content of a node
$xml->group[1]->member[4] = "Hong Meiling";

// add/edit attribute
$xml->group[0]["original-title"] = "東方地霊殿";
$xml->group[1]->member[4]["title"] = "Lazy Gate Guard";


header("Content-Type: text/xml");
$xml->_render("dump");
