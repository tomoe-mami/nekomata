<?php

include("nekomata.php");
include("xhtml.php");

$config =
	array(
		"version"			=> "1.0",
		"encoding"			=> "UTF-8",
		"standalone"		=> FALSE,
		"doctype"			=> TRUE,
		"qualified_name"	=> "html",
		"public_id"			=> "-//W3C//DTD XHTML 1.0 Strict//EN",
		"system_id"			=> "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd",
		"namespace_uri"	=> "http://www.w3.org/1999/xhtml"
	);

$doc = xhtml::_new($config, "xhtml");

$doc
	->head()
		->add_title("Extending Nekomata")
		->meta("http-equiv", "Content-Type", "text/html")
		->_up()
	->body()
		->h1("Extending Nekomata")
		->hr()
		->anchor("http://danbooru.donmai.us/post/show/808799", "Satori Knows Best")
		->p("What is that creature?")
		->form("http://localhost", "post")
			->div()
				->label("Username", "username")
				->input("text", "username")
				->label("Password", "password")
				->input("password", "password")
			->div()
				->label("Mai Waifu is...")
				->select("waifu")
					->option("Yasaka Kanako", "kanako")
					->option("Yagokoro Eirin", "eirin")
					->option("Yakumo Yukari", "yukari")
					->option("What are these old hags doing here?", "no wai")
					->_up()
				->_up()
			->div()
				->label("Message", "message", array("style" => "display: block;"))
				->textarea("message", "Help me, Eirin!!!")
				->_up()
			->div()
				->input("submit", "ok", "OK")
				->_up()
			->_up()
		->h2("Since I'm Nazrin?")
		->anchor("http://danbooru.donmai.us/post/show/808228/")
			->image("http://danbooru.donmai.us/data/preview/81de22a38b457017bab4f207f704ee96.jpg", "uh?")
			->_up()
		->anchor("http://danbooru.donmai.us/post/show/808259")
			->image("http://danbooru.donmai.us/data/preview/3ca53dc8cf640a55d4a460f22155b7d7.jpg")
			->_up();

$doc->body->_first_child()->_insert("span", gmdate(DateTime::ATOM));
header("Content-Type: application/xhtml+xml");
$doc->_render("dump", "tidy");
