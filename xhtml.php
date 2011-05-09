<?php

class xhtml extends nekomata
{
	public static function _new($config = FALSE, $class = "xhtml")
	{
		return(parent::_new($config, $class));
	}

	public function head($value = parent::fork, $params = array())
	{
		$root = $this->_root();

		if (isset($root->head)) return($root->head);
		else $headnode = $root->_create("head", $value, $params);

		return($headnode);
	}

	public function body($value = parent::fork, $params = array())
	{
		$root = $this->_root();

		if (isset($root->body)) return($root->body);
		else $bodynode = $root->_create("body", $value, $params);

		return($bodynode);
	}

	public function add_title($text, $separator = " / ")
	{
		$head = $this->head();
		if (isset($head->title)) $head->title .= $separator.$text;
		else $head->_create("title", $text);

		return($head);
	}

	public function meta($type, $name, $content, $param = NULL)
	{
		$head = $this->head();

		if (isset($head->link)) $ref = $head->link;
		elseif (isset($head->style)) $ref = $head->style;
		elseif (isset($head->script)) $ref = $head->script;
		else $ref = NULL;

		$attrib = array($type => $name, "content" => $content);

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		if ($ref === NULL)
			$head->_create("meta", NULL, $attrib);
		else
			$ref->_insert("meta", NULL, $attrib, NULL, FALSE);

		return($head);
	}

	public function link($rel, $url, $param = NULL)
	{
		$head = $this->head();

		if (isset($head->style)) $ref = $head->style;
		elseif (isset($head->script)) $ref = $head->script;
		else $ref = NULL;

		$attrib = array("rel"	=> $rel, "href" => $url);

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		if ($ref === NULL)
			$head->_create("link", NULL, $attrib);
		else
			$ref->_insert("link", NULL, $attrib, NULL, FALSE);

		return($head);
	}

	public function style($data, $isurl = TRUE, $param = NULL)
	{
		$head = $this->head();

		if (isset($head->script)) $ref = $head->script;
		else $ref = NULL;

		if ($ref === NULL || $isurl)
		{
			if ($isurl)
				$this->link("stylesheet", $data, $param);
			else
				$head->_create("style", $data, $param);
		}
		else $ref->_insert("style", $data, $param, FALSE);

		return($head);
	}

	public function form($action, $method = "post", $param = NULL, $multipart = FALSE)
	{
		$attrib = array("action" => $action, "method" => $method);

		if ($multipart)
			$attrib["enctype"] = "multipart/form-data";

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("form", parent::fork, $attrib));
	}

	public function image($source, $alt = NULL, $param = NULL)
	{
		$attrib = array("src" => $source, "alt" => empty($alt) ? "Image" : $alt);

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("img", NULL, $attrib));
	}

	public function input($type, $name, $value = NULL, $param = NULL)
	{
		$attrib =
			array(
				"type"	=> $type,
				"name"	=> $name,
				"id"		=> $name,
				"value"	=> $value
			);
		
		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("input", NULL, $attrib));
	}

	public function textarea($name, $value = NULL, $param = NULL)
	{
		$attrib = array("name" => $name, "id" => $name);
		
		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("textarea", $value, $attrib));
	}

	public function hr($param = NULL)
	{
		return($this->_create("hr", NULL, $param));
	}

	public function anchor($url, $text = parent::fork, $param = NULL)
	{
		$attrib = array("href" => $url);
		
		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("a", $text, $attrib));
	}

	public function label($text, $for = NULL, $param = NULL)
	{
		$attrib = array();

		if (!empty($for)) $attrib["for"] = $for;

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("label", $text, $attrib));
	}

	public function select($name, $param = NULL)
	{
		$attrib = array("name" => $name,"id" => $name);

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("select", self::fork, $attrib));
	}

	public function option($label, $value = FALSE, $selected = FALSE, $param = NULL)
	{
		$attrib = array();
		if ($value !== FALSE) $attrib["value"] = $value;
		if ($selected) $attrib["selected"] = "selected";

		if (!empty($param) && is_array($param))
			$attrib = array_merge($attrib, $param);

		return($this->_create("option", $label, $attrib));
	}
}
