Nekomata
========

Nekomata is a library for constructing well-formed XML/HTML document. It is
based on SimpleXMLElement.

Usage Examples
--------------

See [this example][atom-neko] for a full atom feed example and
[this example][xhtml-neko] for a full XHTML+RDFa example.

[atom-neko]: https://gist.github.com/rumia/5175049
[xhtml-neko]: https://gist.github.com/rumia/5176220


A simple XML document:

```php
<?php
$doc = Nekomata::create(array('qualified_name' => 'NameOfYourRootNode'));

header('Content-Type: text/xml');
$doc->render('php://output');
```

Result:

```xml
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<NameOfYourRootNode/>
```

Sample of identi.ca's FOAF document:

```php
<?php
$doc = Nekomata::create(array(
    'qualified_name' => 'rdf:RDF',
    'namespaces' => array(
        '_'     => 'http://xmlns.com/foaf/0.1/',
        'rdf'   => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfs'  => 'http://www.w3.org/2000/01/rdf-schema#',
        'geo'   => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
        'bio'   => 'http://purl.org/vocab/bio/0.1/',
        'sioc'  => 'http://rdfs.org/sioc/ns#'
    )
));

$doc
    ->_('PersonalProfileDocument', true, array('rdf:about' => ''))
        ->_('maker', false, array('rdf:resource' => 'http://identi.ca/user/SAMPLE'))
        ->_('primaryTopic', false, array('rdf:resource' => 'http://identi.ca/user/SAMPLE'))
        ->_()
    ->_('Agent', true, array('rdf:about' => 'http://identi.ca/user/SAMPLE'))
        ->_('homepage', false, array('rdf:resource' => 'http://example.com/SAMPLE'))
        ->_('bio:olb', 'This is an example user');

header('Content-Type: text/xml');
$doc->render('php://output', false, true);
```

Result:

```xml
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<rdf:RDF xmlns="http://xmlns.com/foaf/0.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:bio="http://purl.org/vocab/bio/0.1/" xmlns:sioc="http://rdfs.org/sioc/ns#">
  <PersonalProfileDocument rdf:about="">
    <maker rdf:resource="http://identi.ca/user/SAMPLE"/>
    <primaryTopic rdf:resource="http://identi.ca/user/SAMPLE"/>
  </PersonalProfileDocument>
  <Agent rdf:about="http://identi.ca/user/SAMPLE">
    <homepage rdf:resource="http://example.com/SAMPLE"/>
    <bio:olb>This is an example of user</bio:olb>
  </Agent>
</rdf:RDF>
```
