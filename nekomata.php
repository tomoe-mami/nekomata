<?php

class nekomata extends SimpleXMLElement
{
	const fork = TRUE;
	const default_nsprefix = "__";

	public static function _new($config = FALSE, $class = FALSE)
	{
		if (empty($config))
		{
			$config = array(
				"qualified_name"	=> "xml",
				"version"			=> "1.0",
				"encoding"			=> "utf-8"
			);
		}

		if (empty($config["qualified_name"])) return(FALSE);

		$parts = array("xml");
		if (!empty($config["version"]))
			$parts[] = sprintf('version="%s"', $config["version"]);

		if (!empty($config["encoding"]))
			$parts[] = sprintf('encoding="%s"', $config["encoding"]);

		if (isset($config["standalone"]))
			$parts[] = sprintf('standalone="%s"', $config["standalone"] ? "yes" : "no");

		$declaration = sprintf("<?%s?".">\n", implode(" ", $parts));

		if (isset($config["doctype"]) && $config["doctype"])
		{
			$parts	= array("DOCTYPE");
			$parts[] = $config["qualified_name"];
			if (!empty($config["public_id"]))
			{
				$parts[] = "PUBLIC";
				$parts[] = '"'.$config["public_id"].'"';
			}

			if (!empty($config["system_id"]))
			{
				if (empty($config["public_id"])) $parts[] = "SYSTEM";
				$parts[] = '"'.$config["system_id"].'"';
			}

			$doctype = sprintf("<!%s>\n", implode(" ", $parts));
		}
		else $doctype = NULL;

		$parts = array($config["qualified_name"]);

		if (!empty($config["namespace_uri"]))
			$parts[] = sprintf('xmlns="%s"', $config["namespace_uri"]);

		if (!empty($config["extra_namespaces"]))
		{
			foreach ($config["extra_namespaces"] as $nsprefix => $nsuri)
				$parts[] = sprintf('xmlns:%s="%s"', $nsprefix, $nsuri);
		}

		if (!empty($config["root_attributes"]))
		{
			foreach ($config["root_attributes"] as $attrname => $attrval)
				$parts[] = sprintf('%s="%s"', $attrname, $attrval);
		}

		$root = sprintf("<%s></%s>", implode(" ", $parts), $config["qualified_name"]);

		if (empty($class)) $class = __CLASS__;

		$document = simplexml_load_string(
							$declaration.$doctype.$root,
							$class);

		if ($document instanceOf $class)
		{
			if (!empty($config["namespace_uri"]) || !empty($config["extra_namespace"]))
			{
				$namespaces = $document->getDocNamespaces();
				if (!empty($namespaces))
				{
					foreach ($namespaces as $nsprefix => $nsuri)
						$document->registerXPathNamespace(
							(empty($nsprefix) ? self::default_nsprefix : $nsprefix),
							$nsuri);
				}
			}

			return($document);
		}
		else return(FALSE);
	}

	public function _get_dom($returndoc = FALSE)
	{
		if (!($dom = dom_import_simplexml($this)))
			return(FALSE);

		return($returndoc ? $dom->ownerDocument : $dom);
	}

	public function _get_namespaces()
	{
		static $namespacelist;
		
		if (!$namespacelist)
		{
			$found = $this->getDocNamespaces();
			if (empty($found)) $namespacelist = TRUE;
			else $namespacelist = $found;
		}

		return($namespacelist);
	}

	public function _get_nsuri($prefix)
	{
		$nslist = $this->_get_namespaces();

		if (is_array($nslist) && !empty($nslist[$prefix]))
		{
			if (!isset($nslist[$prefix]) && $prefix == $this->_defaultns())
				return(@$nslist[""]);
			else return($nslist[$prefix]);
		}

		return(FALSE);
	}

	public function _get_nsprefix($uri)
	{
		$nslist = $this->_get_namespaces();

		if (is_array($nslist))
			return(array_search($uri, $nslist));

		return(FALSE);
	}

	protected function _parse_name($name)
	{
		$parts	= explode(":", $name);

		if (count($parts) > 1)
		{
			$nsuri = $this->_get_nsuri($parts[0]);
			$parsed =
				array(
					"nsprefix"		=> (empty($nsuri) ? NULL : $parts[0]),
					"nsuri"			=> $nsuri,
					"name"			=> $parts[0].":".$parts[1]
				);
		}
		else $parsed = 
			array("nsprefix" => NULL, "nsuri" => NULL, "name" => $parts[0]);

		return($parsed);
	}

	public function _add_child($tag, $value = self::fork)
	{
		$parsed = $this->_parse_name($tag);
		if (!empty($parsed["name"]))
			return($this->addChild(
				$parsed["name"],
				($value === self::fork ? NULL : $value),
				$parsed["nsuri"])
			);
		return(FALSE);
	}

	public static function _escape_text($text)
	{
		return(preg_replace('/\&(?!amp;|lt;|gt;|\#\d+;)/', '&amp;', $text));
	}

	public function _create(
		$tag,
		$tagvalue	= self::fork,
		$attributes	= NULL,
		$nsprefix	= NULL,
		$safe			= FALSE)
	{
		if (!empty($nsprefix))
			$tag = (strpos($tag, ":") === FALSE ? $nsprefix.":" : NULL).$tag;

		if (is_array($tagvalue))
		{
			foreach ($tagvalue as $value)
				$this->_create(
					$tag, $value, $attributes, $nsprefix, $safe
				);
			return($this);
		}

		if ($tagvalue !== NULL && $tagvalue !== TRUE
			&& $tagvalue !== FALSE && $tagvalue !== self::fork
			&& !$safe)
			$tagvalue = $this->_escape_text($tagvalue);

		$node = $this->_add_child($tag, $tagvalue);

		if ($node === FALSE) return(FALSE);

		if (!empty($attributes)
			&& (is_array($attributes)
			|| $attributes instanceOf stdClass))
		{
			foreach ($attributes as $attrkey => $attrvalue)
				$node->_add_attribute($attrkey, $attrvalue);
		}

		if ($tagvalue === self::fork)
			return($node);
		else return($node->_up());
	}

	public function _remove()
	{
		$dom = $this->_get_dom();
		if ($dom->parentNode)
			return($dom->parentNode->removeChild($dom));
		else return(FALSE);
	}

	public function _add_attribute($key, $value)
	{
		if ($value !== FALSE)
		{
			$parsed = $this->_parse_name($key);
			if (!empty($parsed["name"]))
				$this->addAttribute($parsed["name"], $value, $parsed["nsuri"]);
		}

		return($this);
	}

	public function _set_attribute($arg1, $arg2 = NULL)
	{
		$curdom = $this->_get_dom();

		if (!method_exists($curdom, "setAttribute"))
			return(FALSE);

		if (is_array($arg1))
			$attributes = $arg1;
		else $attributes = array($arg1 => $arg2);

		foreach ($attributes as $attrname => $attrvalue)
			$curdom->setAttribute($attrname, $attrvalue);

		return($this);
	}

	public function _remove_attribute($names)
	{
		$curdom = $this->_get_dom();
		if (!method_exists($curdom, "removeAttribute"))
			return(FALSE);

		if (!is_array($names)) $names = array($names);
		foreach ($names as $attrname)
			$curdom->removeAttribute($attrname);

		return($this);
	}

	public function _value($newvalue = FALSE)
	{
		$curdom = $this->_get_dom();
		if (!property_exists($curdom, "nodeValue"))
			return(FALSE);

		$curdom->nodeValue = $newvalue;
		return($this);
	}

	public function _fragment($string)
	{
		if (empty($string)) return($this);

		if (!($curdom = $this->_get_dom()))
			return(FALSE);

		$fragment = $curdom->ownerDocument->createDocumentFragment();
		$fragment->appendXML($string);
		$curdom->appendChild($fragment);

		return($this);
	}

	public function _construct_pi_param($param)
	{
		$arg = array();
		foreach ($param as $attr => $value)
			$arg[] = sprintf('%s="%s"', $attr, $value);
		return(implode(" ", $arg));
	}

	public function _pi($name, $param, $beforeroot = FALSE)
	{
		if (!($curdom = $this->_get_dom()))
			return(FALSE);

		$pi = $curdom->ownerDocument->createProcessingInstruction(
			$name,
			(is_array($param) ? $this->_construct_pi_param($param) : $param));

		if ($beforeroot)
		{
			$node = $this->_path("/child::*[position()=1]")->_get_dom();
			$curdom->ownerDocument->insertBefore($pi, $node);
		}
		else $curdom->appendChild($pi);
		
		return($this);
	}

	public function _comment($text)
	{
		if (empty($text)) return($this);

		if (!($curdom = $this->_get_dom()))
			return(FALSE);

		$comment = $curdom->ownerDocument->createComment(
			str_replace("--", "-".chr(194).chr(173)."-", $text));

		$curdom->appendChild($comment);

		return($this);
	}

	public function _cdata($text)
	{
		if (empty($text)) return($this);

		if (!($curdom = $this->_get_dom()))
			return(FALSE);

		$cdata = $curdom->ownerDocument->createCDATASection($text);
		$curdom->appendChild($cdata);

		return($this);
	}

	public function _text($text)
	{
		if (empty($text)) return($this);

		if (!($curdom = $this->_get_dom()))
			return(FALSE);

		$textnode = $curdom->ownerDocument->createTextNode($text);
		$curdom->appendChild($textnode);

		return($this);
	}

	public static function _valid_node($node)
	{
		if (!is_object($node)) return(FALSE);

		return($node instanceOf nekomata);
	}

	public function _attach($ext, $before = FALSE)
	{
		if (!$this->_valid_node($ext)) return(FALSE);
		
		$thisdom = $this->_get_dom();
		$extdom	= $ext->_get_dom();

		$newnode = $thisdom->ownerDocument->importNode($extdom, TRUE);
		if ($newnode instanceOf DOMNode)
		{
			if ($before && $thisdom->parentNode instanceOf DOMNode)
				$thisdom->parentNode->insertBefore($newnode, $thisdom);
			else $thisdom->appendChild($newnode);
		}

		return($this);
	}

	public function _insert(
		$tag,
		$tagvalue = self::fork,
		$attribute = NULL,
		$nsprefix = NULL,
		$after = FALSE)
	{
		if (!($curdom = $this->_get_dom()))
			return(FALSE);

		if ($after)
		{
			if (!$curdom->nextSibling)
				return($this->_up()->_create($tag, $tagvalue, $attribute, $nsprefix));
			else $refnode = $curdom->nextSibling();
		}
		else $refnode = $curdom;

		$parsed = $this->_parse_name((empty($nsprefix) ? NULL : $nsprefix.":").$tag);
		if (!empty($parsed["nsuri"]))
		{
			$elem = $curdom->ownerDocument->createElementNS(
						$parsed["nsuri"],
						$parsed["name"],
						($tagvalue === self::fork ? NULL : $tagvalue)
					);

		}
		else
		{
			$elem = $curdom->ownerDocument->createElement(
						$tag,
						($tagvalue === self::fork ? NULL : $tagvalue)
					);
		}

		$node = $curdom->parentNode->insertBefore($elem, $refnode);
		if ($node instanceOf DOMElement)
		{
			$sxenode = simplexml_import_dom($node, get_class($this));
			if (!empty($attribute))
			{
				foreach ($attribute as $attrname => $attrval)
					$sxenode->_add_attribute($attrname, $attrval);
			}
		}
		else return(FALSE);

		return($tagvalue === self::fork ? $sxenode : $this);
	}

	public function _path($query, $index = 0)
	{
		$found = $this->xpath($query);
		if (is_array($found))
			return(isset($found[$index]) ? $found[$index] : NULL);
		else return($found);
	}

	public function _mark(&$var)
	{
		$var = $this;
		return($this);
	}

	public function _base($node = NULL)
	{
		static $basenode;

		if ($node === TRUE) $basenode = $this;
		else if ($node !== NULL) $basenode = $node;
		return($basenode);
	}

	public function _up()
	{
		return($this->_path(".."));
	}

	public function _first_child()
	{
		return($this->_path("child::*[position()=1]"));
	}

	public function _root()
	{
		return($this->_path("/child::*[position()=1]"));
	}

	public function _last_child()
	{
		return($this->_path("child::*[position()=last()]"));
	}

	public function _select_child($index = 0)
	{
		if (($index = (int) $index) < 0) $index = 0;
		return($this->_path("child::*[position()=".($index+1)."]"));
	}

	public function _next_sibling()
	{
		return($this->_path("following-sibling::*[position()=1]"));
	}

	public function _previous_sibling()
	{
		return($this->_path("preceding-sibling::*[position()=1]"));
	}

	public function _render()
	{
		$args = func_get_args();
		$doc	= $this->_get_dom(TRUE);

		if (in_array("tidy", $args))
			$doc->formatOutput = TRUE;

		if (in_array("dump", $args))
			return($doc->save("php://output"));

		$func = (in_array("html", $args) ? "saveHTML" : "saveXML");

		return($doc->{$func}());
	}

	public function __call($name, $params)
	{
		if (!strncmp($name, "_", 1)) return(FALSE);

		switch (count($params))
		{
			case 0: return($this->_create($name));
			case 1:
				if (is_array($params[0]))
					return($this->_create($name, self::fork, $params[0]));
				else return($this->_create($name, $params[0]));
			case 2:
				if (is_array($params[1]))
					return($this->_create($name, $params[0], $params[1]));
				else return($this->_create($name, $params[0], NULL, $params[1]));

			case 3:
				return($this->_create($name, $params[0], $params[1], $params[2]));
		}
	}
}
