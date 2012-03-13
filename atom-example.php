<?php
// vim: ts=3 sw=3 et

include('nekomata.php');

$config =
   array(
      'version'         => '1.0',
      'encoding'        => 'UTF-8',
      'standalone'      => FALSE,
      'doctype'         => TRUE,
      'qualified_name'  => 'feed',
      'public_id'       => NULL,
      'system_id'       => NULL,
      'namespaces'      => 
         array(
            ''   => 'http://www.w3.org/2005/Atom'
         )
   );

$doc = nekomata::_new($config);

$doc
   ->title('Example Feed')
   ->link(FALSE, array('href' => 'http://example.org'))
   ->updated('1987-12-06T07:00:00+0700')
   ->author()
      ->name('Ocelot')
      ->_up()
   ->id('tag:ocelot,1337:/boo/yeah')
   ->entry()
      ->title('Look Ma, I Can Do Atom')
      ->link(FALSE, array('href' => 'http://example.org/2003/12/13/atom03'))
      ->id('tag:ocelot,1337:/boo/yeah/sweet')
      ->updated('2012-03-13T18:04:10+0700')
      ->summary('Atom or: How I Learned to Stop Worrying and Love the Bomb')
      ->content(array('type' => 'xhtml'))
         ->div(array('xmlns' => 'http://www.w3.org/1999/xhtml'))
            ->p('All work and no play makes Jack a dull boy');

header('Content-Type: application/atom+xml');
$doc->_render('dump');
