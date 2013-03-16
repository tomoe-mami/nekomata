<?php
// vim: ts=3 sw=3 et

class Nekomata extends SimpleXMLElement
{
   const XMLNS_URI         = 'http://www.w3.org/2000/xmlns/',
         DEFAULT_NS_PREFIX = '_';

   public static function create(array $options = null, $class = null)
   {
      static $implementation;

      $default =
         array(
            'system_id'       => null,
            'public_id'       => null,
            'qualified_name'  => 'root',
            'encoding'        => 'UTF-8',
            'version'         => '1.0',
            'standalone'      => false,
            'namespaces'      => array(),
            'attributes'      => array(),
            'create_doctype'  => false
         );

      if (isset($options))
         $options = $options + $default;
      else
         $options = $default;

      if (!isset($options['namespaces'][self::DEFAULT_NS_PREFIX]))
         $default_ns_uri = null;
      else
      {
         $default_ns_uri = $options['namespaces'][self::DEFAULT_NS_PREFIX];
         unset($options['namespaces'][self::DEFAULT_NS_PREFIX]);
      }

      if (isset($options['create_doctype']) && $options['create_doctype'])
      {
         if (!$implementation instanceOf DOMImplementation)
            $implementation = new DOMImplementation();

         $doctype = $implementation->createDocumentType(
               $options['qualified_name'],
               $options['public_id'],
               $options['system_id']
            );

         $document = $implementation->createDocument(
               $default_ns_uri,
               $options['qualified_name'],
               $doctype
            );
      }
      else
      {
         $document = new DOMDocument;
         $root = $document->createElement($options['qualified_name']);
         if (strlen($default_ns_uri))
            $root->setAttributeNS(self::XMLNS_URI, 'xmlns', $default_ns_uri);

         $document->appendChild($root);
      }

      $document->encoding = $options['encoding'];
      $document->xmlVersion = $options['version'];
      $document->xmlStandalone = $options['standalone'];

      if (!empty($options['namespaces']))
      {
         foreach ($options['namespaces'] as $prefix => $uri)
         {
            $document->documentElement->setAttributeNS(
               self::XMLNS_URI,
               (strpos($prefix, ':') === false ? 'xmlns:' : '') . $prefix,
               $uri);
         }
      }

      if (!isset($class))
      {
         if (version_compare(PHP_VERSION, '5.3.0') >= 0)
            $class = get_called_class();
         else
            $class = __CLASS__;
      }

      $neko = simplexml_import_dom($document, $class);

      if (strlen($default_ns_uri))
         $neko->registerXPathNamespace(self::DEFAULT_NS_PREFIX, $default_ns_uri);

      if (!empty($options['attributes']))
         $neko->setAttributes($document->documentElement, $options['attributes']);

      return $neko;
   }

   public function root()
   {
      return simplexml_import_dom($this->dom(true), get_class($this));
   }

   public function parent()
   {
      $dom = $this->dom();

      if ($dom->parentNode instanceOf DOMNode)
         return simplexml_import_dom($dom->parentNode, get_class($this));
      else
         return false;
   }

   public function dom($return_owner = false)
   {
      $dom = dom_import_simplexml($this);
      if (!$dom instanceOf DOMNode)
      {
         throw new Exception(
            'Unable to get DOMElement representation of current node'
         );
      }

      return ($return_owner ? $dom->ownerDocument : $dom);
   }

   protected function parseQualifiedName($qualified_name, $reference = null)
   {
      if (strpos($qualified_name, ':') === false)
      {
         return array(
            'prefix' => null,
            'uri' => null,
            'local_name' => $qualified_name,
            'qualified_name' => $qualified_name
         );
      }
      else
      {
         list ($ns_prefix, $local_name) = explode(':', $qualified_name);

         if ($ns_prefix === '' || $ns_prefix === self::DEFAULT_NS_PREFIX)
            $ns_prefix = null;

         if (!isset($reference)) $reference = $this->dom();

         $ns_uri = $reference->ownerDocument->lookupNamespaceURI($ns_prefix);
         return array(
            'prefix' => $ns_prefix,
            'uri' => $ns_uri,
            'local_name' => $local_name,
            'qualified_name' => $ns_prefix . ':' . $local_name
         );
      }
   }

   protected function appendText(DOMElement $node, $value)
   {
      $text = $node->ownerDocument->createTextNode($value);
      $node->appendChild($text);
   }

   protected function setAttributes(DOMElement $node, array $attributes)
   {
      foreach ($attributes as $key => $value)
      {
         $key = $this->parseQualifiedName($key, $node);

         if (empty($key['uri']))
            $node->setAttribute($key['qualified_name'], $value);
         else
            $node->setAttributeNS($key['uri'], $key['qualified_name'], $value);
      }
   }

   public function add(
      $node_name,
      $node_value = true,
      array $attributes = null)
   {
      $dom = $this->dom();
      $tag = $this->parseQualifiedName($node_name, $dom);

      if (empty($tag['uri']))
         $node = $dom->ownerDocument->createElement($tag['qualified_name']);
      else
      {
         $node = $dom->ownerDocument->createElementNS(
            $tag['uri'],
            $tag['qualified_name']);
      }

      if (!is_bool($node_value)) $this->appendText($node, $node_value);
      if (!empty($attributes)) $this->setAttributes($node, $attributes);
      $dom->appendChild($node);

      if ($node_value !== true)
         return $this;
      else
         return simplexml_import_dom($node, get_class($this));
   }

   public function insert(
      $node_name,
      $node_value = true,
      array $attributes = null,
      $insert_after = false)
   {
      $dom = $this->dom();

      if (!$insert_after)
         $reference = $dom;
      else
      {
         if (!$dom->nextSibling)
            return $this->parent()->add($node_name, $node_value, $attributes);
         else
            $reference = $dom->nextSibling;
      }

      $tag = $this->parseQualifiedName($node_name, $dom);

      if (empty($tag['uri']))
         $node = $dom->ownerDocument->createElement($tag['qualified_name']);
      else
      {
         $node = $dom->ownerDocument->createElementNS(
            $tag['uri'],
            $tag['qualified_name']);
      }

      if (!empty($attributes)) $this->setAttributes($node, $attributes);

      $dom->parentNode->insertBefore($node, $reference);
      if (is_bool($node_value))
         return simplexml_import_dom($node, get_class($this));
      else
      {
         $this->appendText($node, $node_value);
         return $this;
      }
   }

   public function fragment($string, $is_soup = false)
   {
      $current_dom = $this->dom();
      if ($is_soup)
      {
         libxml_use_internal_errors(true);
         $temp = new DOMDocument;

         $encoding = $current_dom->ownerDocument->encoding;
         $temp->loadHTML(
            '<?xml version="1.0" standalone="yes" encoding="' .
            $encoding .
            "\">\n" .
            $string);

         $temp->encoding = $encoding;
         $xpath = new DOMXPath($temp);
         $found = $xpath->query('/html/body/*');

         $string = '';
         if ($found instanceOf DOMNodeList && $found->length > 0)
         {
            foreach ($found as $child)
               $string .= $temp->saveXML($child);
         }
      }

      if (strlen($string) > 0)
      {
         $fragment = $current_dom->ownerDocument->createDocumentFragment();
         if ($fragment instanceOf DOMDocumentFragment)
         {
            $fragment->appendXML($string);
            $current_dom->appendChild($fragment);
         }
      }

      return $this;
   }

   public function grab(&$var)
   {
      $var = $this;
      return $this;
   }

   public function render($file = null, $is_html = false, $tidy = false)
   {
      $dom = $this->dom(true);

      if ($tidy)
      {
         $dom->formatOutput = true;
         $dom->preserveWhiteSpace = false;
      }

      if (!strlen($file))
         return ($is_html ? $dom->saveHTML() : $dom->saveXML());
      else
         return ($is_html ? $dom->saveHTMLFile($file) : $dom->save($file));
   }

   public function _(
      $node_name = null,
      $node_value = true,
      array $attributes = null)
   {
      if (!isset($node_name))
         return $this->parent();
      else
         return $this->add($node_name, $node_value, $attributes);
   }
}
