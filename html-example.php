<?php
// vim: ts=3 sw=3 et
include("nekomata.php");

$config =
   array(
      "version"         => "1.0",
      "encoding"        => "UTF-8",
      "standalone"      => FALSE,
      "options"         => 0,
      "doctype"         => TRUE,
      "qualified_name"  => "html",
      "public_id"       => "-//W3C//DTD HTML 4.01 Transitional//EN",
      "system_id"       => "http://www.w3.org/TR/html4/loose.dtd",
   );

$html = nekomata::_new($config);

$html
   ->head()
      ->title("nekomata html example")
      ->meta(NULL, array("http-equiv" => "Content-Type", "content" => "text/html"))
      ->style(".inset { float: left; margin: 0px 10px; }", array("type" => "text/css"))
      ->_up()
   ->body()
      ->h1("化け猫 (ねこまた) (Forked Cat)")
      ->img(NULL, array("class" => "inset", "src" => "http://upload.wikimedia.org/wikipedia/commons/thumb/2/21/SekienNekomata.jpg/280px-SekienNekomata.jpg", "alt" => "nekomata"))
      ->p("A bakeneko whose tail has grown long and forked in two; able to manipulate the dead like puppets and seen as the cause of fires and other unexplainable occurrences.");

$htmlstring = <<<EOF
<p>According to Japanese folklore, a cat (neko) that has lived for a long time can become a kind of
<a href="http://en.wikipedia.org/wiki/Yokai">youkai</a> called a <strong>nekomata</strong> (猫叉).
It was believed that after a cat reached ten years of age, its tail would slowly split into two tails,
and, along the way, it would develop magic powers, primarily those of necromancy and shamanism.
Nekomata also have an ability to shape shift into a human form and are generally hostile to humans.</p>
<p>There is also one kind of Nekomata that lived in Nabeshimahan （鍋島藩）, which lived long enough
to split its tail six times, resulting in seven tails. It is the most powerful nekomata in Japan.</p>

EOF;


$html->body->_fragment($htmlstring);

$html->body
   ->h2("History")
   ->p("In the early 17th century the Japanese used cats to kill off the rats and ".
   "mice that were threatening the silkworms. During this time it was illegal ".
   "to buy or sell cats. Most of the cats in Japan were set free to roam around ".
   "the cities. Stories about these street cats became legends over time. There ".
   "are many stories about the supernatural abilities of the bake-neko: talking, ".
   "walking on their two rear legs, shapeshifting, flying, killing people, and ".
   "even resurrecting the dead. Because of the stories about the bake-neko some ".
   "Japanese people may have cut their cat’s tail off to stop them from becoming ".
   "a bake-neko. Cats that were caught drinking lamp oil were also considered to ".
   "be bake-neko. Cats may have regularly been drinking lamp oil as it was based on fish oil.");

header("Content-Type: text/html");
$html->_render("dump", "html");
