<?php

/**
 * Head Class
 * 
 * Creates the Doctype and head tags for an HTML page built with CodeIgniter.
 * 
 * @license		GNU General Public License
 * @author		Adam Fairholm
 * @link		http://code.google.com/p/codeigniter-head-library
 * @email		adam.fairholm@gmail.com
 * 
 * @file		Head.php
 * @version		1.0
 * @date		10/30/2009
 * 
 * Copyright (c) 2009
 */

class Head
{
	var $close_head					= TRUE;							//Should we use the closing </head> tag?
	var $show_errors 				= TRUE;							//Should we throw a hissy fit?
	var $debug						= FALSE;						//Should we debug?
	var $output_string				= FALSE;						//Should we output this to a string? If not, then we'll use a constant
	var $constant_name				= "HEAD";						//Name of constant to save to if we are going that route
	
	var $js_location				= "js/";						//Location of the Javascript files
	var $css_location				= "css/";						//Location of the CSS files
	
	var $packs 						= array();						//Packages to include
	var $packages					= array();						//Packages
		
	var $doctype					= 'xhtml1-strict';				//Default Doctype
	var $xml_doctypes 				= array('xhtml11',				//Doctypes that require some special XHTML love
											'xhtml1-strict',
											'xhtml1-trans',
											'xhtml1-frame');
						
	var $use_base					= FALSE;						//Should we use the <base> tag?
	var $base_target				= '';							//Target for the base, if needed
	var $base_ref					= '';							//Href for the base. Uses base_url in config if blank
	
	var $site_title					= '';							//The title of the site
	var $title 						= '';							//The title page
	var $title_append				= TRUE;							//Should we append this to the site title?
	var $title_append_str			= ' - ';						//How we should append, if necessary
	
	var $use_meta					= TRUE;							//I don't know, maybe someone doesn't want to
	var $meta_content				= "text/html; charset=utf-8";	//Content type for meta data
	var $meta_language				= "en";							//Language for meta data
	var $meta_author				= '';							//Author name for the meta data
	var $meta_description			= '';							//Description for the meta data
	var $meta_keywords				= '';							//Keywords for the meta data
	var $meta						= array();						//Additional Metadata
	
	var $use_favicon				= TRUE;							//Should we use the favicon?
	var $favicon_location 			= "images/favicon.ico"; 		//Location of the favicon if we're using it
	
	var $ga_tracking_id				= '';							//Google Analytics Tracking Code
	
	var $defaults					= array();
	var $css						= array();
	var $js							= array();
	var $inline						= array();
	var $feeds						= array();
	var $misc						= array();						//Misc items to add in
  	var $jquery           			= array();            			//JQuery Items
  	
  	var $jquery_file				= "jquery.js";					//Name of JQuery file. You know the people like to make 'em crazy

	// --------------------------------------------------------------------------
	
	function Head( $config = array() )
	{
		$this->CI =& get_instance();
	
		//We need the html helper
		if( ! function_exists('br') )
			$this->CI->load->helper('html');
			
		//Get some handy URLs
		$this->base_url		= base_url();
		$this->link_url 	= site_url();
		
		//Initialize the configs
		if(count($config) > 0)
		{
			$this->initialize($config);
		}
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Initialize Configs
	 *
	 * @param	array [$config] assoc array of configs to be initialized
	 * @return	void
	 */
	function initialize($config)
	{
		foreach($config as $key => $var)
		{
			$this->$key = $var;
		}
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render Head <head></head>
	 * 
	 * Creates our doctype and head calls for a nice clean head to the document.
	 *
	 * @param	array [$passed_config]
	 * @return	void or string
	 */
	function render_head($passed_config = array())
	{
		if(count($passed_config) > 0)
		{
			$this->initialize($passed_config);
		}

		//Start the party
		
		$html  = $this->render_doctype();
		
		$html .= $this->render_html().$this->bump(FALSE).'<head>'.$this->bump();
		
		if($this->use_base)
		{
			$html .= $this->render_base();
		}
		
		if($this->use_meta)
		{
			$html .= $this->render_meta();
		}
		
		if($this->use_favicon)
		{
			$html .= $this->render_favicon();
		}

    	$html .= $this->include_default_packages();
    	
    	array_unique($this->packs);
		
		$this->process_packages();
		
		$html .= $this->render_items("css");
		
		//Jquery
		//Jquery likes to be loaded first, so if we are using it, let's get it in there
		if( array_key_exists($this->jquery_file, $this->js) || ! empty($this->jquery) )
		{
			$html .= '<script type="text/javascript" src="'.$this->base_url.$this->js_location.$this->jquery_file.'"></script>'.$this->bump(FALSE);
			
			unset($this->js[$this->jquery_file]);
		}
		
		$html .= $this->render_items("js");
		
    	$html .= $this->render_jquery();
    	
		
		if(count($this->feeds) > 0)
		{
			foreach($this->feeds as $feed_link)
			{
				$html .= $feed_link.$this->bump(FALSE);
			}
			
			$html .= $this->bump(FALSE);
		}

		if(count($this->inline) > 0)
		{
			foreach($this->inline as $inline_code)
			{
				$html .= $inline_code;
			}
			
			$html .= $this->bump();
		}
		
		$html .= $this->render_misc();
		
		$html .= $this->render_title();
		
		$html .= $this->render_ga();
		
		if( $this->close_head )
		{
			$html .= '</head>'.$this->bump();
		}
		
		//Debug
		if($this->debug == TRUE)
			$this->check_head();
		
		//Final out
		if( $this->output_string == TRUE )
			return $html;
		else
			define($this->constant_name, $html);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render Doctype
	 * 
	 * Get our doctype out there.
	 * Uses the html helper doctype function and accesses doctype config array
	 *
	 * @return	string doctype
	 */
	function render_doctype()
	{		
		$doc = doctype($this->doctype);
		
		if( ! trim($doc))
		{
			$this->handle_error('Invalid Doctype');
		}
		else
		{
			return $doc.$this->bump();
		}
		
	}

	// --------------------------------------------------------------------------

	/**
	 * Render the html opening tag
	 * 
	 * @return	string
	 */
	function render_html()
	{
		if(in_array($this->doctype, $this->xml_doctypes))
		{
			return '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$this->meta_language.'" lang="'.$this->meta_language.'">';
		}
		else
		{
			return '<html>';
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Render the base tag
	 *
	 * @return	string
	 */
	function render_base()
	{
		if( ! $this->base_ref)
		{
			$base = $this->CI->config->item('base_url');
		}
		else
		{
			$base = $this->base_ref;
		}
			
		$out = '<base href="'.$base.'"';
		
		if($this->base_target)
		{
			$out .= ' '.$this->base_target;
		}
	
		return $out .= ' />'.$this->bump();
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render the title tag
	 *
	 * @return	string
	 */
	function render_title()
	{
		$out = '<title>';
		
		if($this->title_append)
		{
			$out .= $this->site_title.$this->title_append_str;
		}
		
		return $out.= $this->title.'</title>'.$this->bump();
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render Google Analytics tracking code
	 *
	 * @return 	string
	 */
	function render_ga()
	{
		if( $this->ga_tracking_id != '' )
		{
			return '
			
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \''.$this->ga_tracking_id.'\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>				
			';
		}
		
		return null;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render metadata
	 *
	 * @return	string
	 */
	function render_meta()
	{
		$out = '';
		
		if($this->meta_content)
		{
			$out .= meta('content-type', $this->meta_content, 'equiv').$this->indent();
		}
	
		if($this->meta_language)
		{
			$out .= meta('content-language', $this->meta_language, 'equiv').$this->indent();
		}
	
		if($this->meta_author)
		{
			$out .= meta('author', $this->meta_author).$this->indent();
		}
		
		if($this->meta_description)
		{
			$out .= meta('description', $this->meta_description).$this->indent();
		}
		
		if($this->meta_keywords)
		{
			$out .= meta('keywords', $this->meta_keywords).$this->indent();
		}
		
		//Take the extra meta and process those
		
		if(count($this->meta) > 0)
		{
			foreach($this->meta as $meta_item)
			{
				$out  .= $meta_item.$this->indent();
			}
		}

		return $out .= $this->bump(FALSE);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Add a meta item
	 * 
	 * Allows you to create a new meta item
	 *
	 * @param	string [$name] name of the meta item
	 * @param	string [$content] meta content
	 * @param	string [$name_or_equiv] "name" or "equiv" meta. Defaults to "name"
	 * @return 	void
	 */
	function add_meta($name, $content, $name_or_equv = "name")
	{
		$this->meta[] = meta($name, $content, $name_or_equv);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render Favicon
	 *
	 * @return	string
	 */
	function render_favicon()
	{
		return '<link rel="shortcut icon" href="'.$this->base_url.$this->favicon_location.'" />'.$this->bump();
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render the CSS and JS
	 *
	 * @return	string
	 */
	function render_items($type)
	{
		$out = '';
	
		foreach($this->$type as $it)
		{
			$out .= $it.$this->bump(FALSE);
		}
		
		return $out.$this->bump(FALSE);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Add misc
	 *
	 * Add pretty much anything into the head document
	 *
	 * @param	string [$code] code to add to head
	 * @return	void
	 */
	function add_misc($code)
	{
		$this->misc[] = $code;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Render the misc items
	 */
	function render_misc()
	{
		$out = "";
	
		if( ! empty($this->misc) )
		{
			foreach( $this->misc as $item )
			{
				$out .= $item.$this->bump();
			}
		}
		
		return $out;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Include a package in the mix
	 *
	 * @param	string [$packs] separated by "|"
	 * @return	void
	 */
	function include_packages($packs)
	{
		$pks = explode("|", $packs);

		foreach($pks as $pk)
		{
			//Check and see if it exists
			if( ! array_key_exists($pk, $this->packages))
			{
				$this->handle_error("Package '".$pk."' is not supported");
			}
			else
			{
				$this->packs[] = $pk;
			}
		}
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Include default packages
	 */
	function include_default_packages()
	{
		foreach( $this->defaults['packages'] as $def_package )
		{
			//Check and see if it exists
			if( ! array_key_exists($def_package, $this->packages) )
			{
				$this->handle_error("Package '".$def_package."' is not supported");
			}
			else
			{
				$this->packs[] = $def_package;
			}
		}
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Process the packages from the config file
	 * 
	 * @return 	void
	 */
	function process_packages()
	{
		//Clean packages
		$defaults = $this->defaults;
		unset($defaults['packages']);
	
		//First, go through the defaults.
		foreach($defaults as $def_file)
		{
			$this->process_file($def_file);
		}
		
		//Now process the packages
		foreach($this->packs as $pck)
		{
			foreach($this->packages[$pck] as $fl)
			{
				$this->process_file($fl);
			}
		}
		
		//Get rid of the duplicates
		$this->css 	= array_unique($this->css);
		$this->js 	= array_unique($this->js);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Takes a filename and determines if it is a css or js, and then takes
	 * The appropriate action
	 * 
	 * @param	string [$filename]
	 * @return	void
	 */
	function process_file($filename)
	{
		$elems = explode(".", $filename);
		
		$filetype = $elems[count($elems)-1];
		
		if($filetype == "css")
		{
			$this->add_css($filename);
		}
		else if($filetype == "js")
		{
			$this->add_js($filename);
		}
		else
		{
			$this->handle_error("Unable to process unknown file type: ".$filename);
		}
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Add a CSS file link tag
	 *
	 * @param	string [$file] filename of the CSS file
	 * @param	string [$media] media type. Defaults to "all"
	 * @return	void
	 */
	function add_css($file, $media="all")
	{
		$this->css[$file] = '<link href="'.$this->base_url.$this->css_location.$file.'" rel="stylesheet" media="'.$media.'" type="text/css" />';
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Add a JS file link tag
	 *
	 * @param	string [$file] file name of the JS file
	 * @return	void
	 */
	function add_js($file)
	{
		$this->js[$file] = '<script type="text/javascript" src="'.$this->base_url.$this->js_location.$file.'"></script>';
	}

	// --------------------------------------------------------------------------

	/**
	 * Add some JQuery code
	 *
	 * @param	string [$code] the JQuery code to be added
	 * @return	void
	 */
	function add_jquery($code)
	{
		$this->jquery[] = $code;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Add some inline CSS code or JS code
	 *
	 * @param	string [$code] The code to inserted
	 * @return	string [$js_or_css] set to "js" or "css"
	 */
	function add_inline($code, $js_or_css)
	{
		$js_or_css = strtolower($js_or_css);
	
		if($js_or_css == "css")
		{
			$this->inline[] = "<style type=\"text/css\">
		$code				
	</style>";
		}
		else if($js_or_css == "js")
		{
			$this->inline[] = '<script type="text/javascript" language="javascript">
			  // <![CDATA[
			  	'.$code.'
			  // ]]>
			</script>';
		} 
	}

	// --------------------------------------------------------------------------

	/**
	 * Render JQuery
	 *
	 * Takes all the JQuery items and spits them out in the document.ready function
	 * 
	 * @return	void
	 */
	function render_jquery()
	{
		if( ! empty($this->jquery) )
	    {
	  		$out = '<script type="text/javascript" language="javascript">
	        // <![CDATA[
	        $(document).ready(function(){
	          ';
	          
	      	foreach($this->jquery as $code)
	      	{
	        	$out .= $code.$this->bump(FALSE);
	      	}
	          
	      	$out .= '
	      	});
	        	// ]]>
	      	</script>
	  		';
	    
	      return $out;
	    }
	    else
	    {
	    	return '';
	    }
	}

	// --------------------------------------------------------------------------
	  
	/**
	 * Add RSS or Atom feed link
	 *
	 * @param	string [$feed] full feed URL
	 * @param	string [$name] feed title
	 * @param	string [$rss_or_atom] RSS or Atom. Defaults to RSS.
	 */
	function add_feed($feed, $name, $rss_or_atom = "rss")
	{
		$rss_or_atom = strtolower($rss_or_atom);
	
		$this->feeds[] = '<link href="'.$feed.'" type="application/'.$rss_or_atom.'+xml" rel="alternate" title="'.$name.'" />';
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Checks the doctype and makes sure the stuff needed in the head is there
	 *
	 * @return 	TRUE on ok, string (ul of errors) if not ok
	 */
	function check_head()
	{
		$errors = '';
	
		//Check all the links for CSS
		foreach($this->css as $file => $link)
		{
			if( ! file_exists("./".$this->css_location.$file))
			{
				$errors .= '<li>'.$file.' not found</li>';
			}
		}

		//Check all the links for JS
		foreach($this->js as $file => $link)
		{
			if( ! file_exists("./".$this->js_location.$file))
			{
				$errors .= '<li>'.$file.' not found</li>';
			}
		}
		
		//Check for favicon
		if($this->use_favicon)
		{
			if( ! file_exists("./".$this->favicon_location))
			{
				$errors .= '<li>Favicon not found</li>';
			}
		}
		
		if(trim($errors))
		{
			show_error("The following errors were encountered in the head area: <ul>".$errors."</ul>");
		}
	}	

	// --------------------------------------------------------------------------
	
	/**
	 * Handles our error and sees if we want to keep quiet or not
	 * so we don't have to do it a million times.
	 * 
	 * @access	private
	 * @return 	void
	 */
	private function handle_error($msg)
	{
		if($this->show_errors)
			show_error($msg);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Provides the bump so we can still have nice and pretty head spacing
	 *
	 * @access	private
	 * @param	string [$new_line] should we add a new line in there?
	 * @return	string
	 */
	private function bump($new_line = TRUE)
	{
		if($new_line)
		{
		return '
		
	';		}
		else
		{
		return '
	';		}
	}
	
	private function indent()
	{
		return '	';
	}

}

/* End of file Head.php */
/* Location: ./application/libraries/Head.php */