<?php

 // --------------------------------------------------------------------------
 // Head Library Configs
 // --------------------------------------------------------------------------

 /**
 * Default configs
 *
 * These configs can override class variables for the head class. You can find
 * These variables listed and commented in the head class file.
 */
  
 $config['meta_author'] = "Adam Fairholm";
 
 $config['meta_description'] = "A test page.";
 
 /**
 * Default elements
 *
 * Need some files loaded for every page? Put them here. They will be loaded first.
 */
 
 $config['defaults'] = array('master.css', 'tinymce/tinymce.js');

 /**
 * Packages
 *
 * Packages work like containers for groups of files needed. For example, the coda slider
 * Needs several Javascipt files, and a css file.
 *
 * Duplicate files in packages will not loaded twice. 
 */
 
 $config['packages']['coda_slider'] = array(
	'coda-slider.css',
	'jquery.js',
	'jquery.scrollTo.js',
	'jquery.localscroll.js',
	'jquery.serialScroll.js',
	'coda-slider.js');
	
 $config['packages']['accordian']		= array('jquery.js', 'accordian.js', 'accordian.css');

/* End of file head.php */
/* Location: ./application/config/head.php */