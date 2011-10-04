<?php
// vim: ts=3 sw=3 et
include("nekomata.php");

$xml = nekomata::_new();

// common usage, create/append new elements inside current active node.
// you can create new element just by using the element name as function/method name,
// and the element value as the first argument

$xml->title("Touhou Project"); // result: <title>Touhou Project</title>

// to set an attribute, use the attribute name as array index
$xml->title["original-title"] = "東方Project"; // result (applied to the element created in example above): <title original-title="東方Project">Touhou Project</title>

// if the first argument is an array, it will be treated as attribute list
$xml->author(array("name" => "ZUN", "website" => "http://www16.big.or.jp/~zun/")); // result: <author name="ZUN" website="http://www16.big.or.jp/~zun/" />

// to create an element with both value and attribute present:
$xml->developer("Team Shanghai Alice", array("original-name" => "上海アリス幻樂団")); // result: <team original-name="上海アリス幻樂団">Team Shanghai Alice</team>


// you can use method chaining to continuously create new element inside the current active node.
// when you use method chaining, if the new element has no value or its value is nekomata::fork or TRUE
// the current active node will be set to the newly created element

$xml
   ->genres()                       // this will create <genres> in the current node (root node)
      ->genre("Shoot 'Em Up")       // this and the one below it will create <genre>blah</genre> inside <genres>
      ->genre("Fighting");

// nekomata::_up() will set parent node as the active node.

$xml
   ->publisher("Team Shanghai Alice")
   ->platforms()
      ->platform("NEC PC-9801", array("url" => "http://en.wikipedia.org/wiki/NEC_PC-9801"))
      ->platform("Windows", array("url" => "http://en.wikipedia.org/wiki/Windows"))
      ->_up()
   ->releases()
      ->release("Highly Responsive to Prayers", array("year" => 1996, "initial" => "yes"))
      ->release("Ten Desires", array("year" => 2011));

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
$xml->group[0]["original-title"] = "東方地霊殿";
$xml->group[1]->member[4] = "Hong Meiling";

$xml->group[1]->_insert("fubar");

// add/edit attribute
$xml->group[1]->member[4]["title"] = "Lazy Gate Guard";


header("Content-Type: text/xml");
$xml->_render("tidy", "dump");
// if you want tidied output, add "tidy" to the arg list when calling nekomata::_render()
// eg: $xml->_render("tidy", "dump");
// the order of argument doesn't matter



// NOTE:
// all nekomata's internal function names are prefixed with underscore
// so try to avoid using xml element name with underscore in front of it.
// if you really need to create element like that, use nekomata::_create()
// eg:
// $xml->_create("_element", "value", array("attr" => "value"));
