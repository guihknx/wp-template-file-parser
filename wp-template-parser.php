<?php
class WP_Template_Meta_Parser
{
	/**
	 * Theme is an ref for style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $theme;

	/**
	 * Theme Name is the template name inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $theme_name;

	/**
	 * Theme URI is the template URI inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $theme_uri;

	/**
	 * Author is the template Author inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $author;

	/**
	 * Author URI is the template Author URI inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $author_uri;

	/**
	 * Description is the template Description located inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $description;
	
	/**
	 * Version is the template Version inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $version;
	
	/**
	 * License is the template License inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $license;
	
	/**
	 * License URI is the template License URI inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $license_uri;
	
	/**
	 * Tags is the template Tags inside style.css file.
	 *
	 * @access public
	 * @var array
	 */
	private $tags = array();
	
	/**
	 * TextDomain is the template TextDomain inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $text_domain;
	
	/**
	 * Buffer is the template Buffer inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $buffer;
	
	/**
	 * Pattern used to search inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $search;

	/**
	 * Pattern used to search inside style.css file.
	 *
	 * @access public
	 * @var var
	 */
	private $data;

	/**
	 * Construct class with template absolute path
	 *
	 * @access public
	 *
	 * @param string $template Absolute path of style.css file
	 * @return void
	 */
	private $type;
	public function __construct( $template, $type )
	{
		$this->type = $type;
		$this->theme = $template;
	}

	public function __get( $property_name )
	{
		return $this->_get_property( $property_name );
	}
	/**
	 * Create an error container for easy call inside class
	 *
	 * @access public
	 *
	 * @param string $msg Message user will see in view
	 * @param string $flag
	 * @return void
	 */
	private function _error($msg='', $flag='')
	{
		return die( '<pre>ERROR: '. htmlentities( $msg ) . '</pre>' );
	}

	/**
	 * Returns file context buffer
	 *
	 * @access private
	 * @uses file_get_contents()
	 *
	 * @return string String with style.css file contents
	 */
	public function fetch_theme_contents()
	{
		if ( ! is_file( $this->theme ) )
			$this->_error( 'File not found!' );

		if ( empty( $this->theme ) || $this->theme == '' )
			$this->_error( 'Not input file.' );

		if ( ! $this->is_wp_theme_file_ext() || ! $this->is_wp_theme_file() )
			$this->_error( 
				'ERROR: That\'s not WordPress valid stylesheet file.' );
		
		return file_get_contents( $this->theme );
	}

	/**
	 * Return if passed file have .css extention
	 *
	 * @access private
	 *
	 * @return bool
	 */
	private function is_wp_theme_file_ext()
	{
		return ( explode( '.', 
			end ( explode( '/', 
				$this->theme ) ) )[1] == 'css' ) ? true : false; 
	} 

	/**
	 * Return true if passed filename is style
	 *
	 * @access private
	 *
	 * @return bool
	 */	
	private function is_wp_theme_file()
	{
		return ( explode( '.', 
			end ( explode( '/', 
				$this->theme ) ) )[0] == 'style' ) ? true : false; 
	} 

	/**
	 * Search inside file matching the pattern passed as param
	 *
	 * @access private
	 * @uses preg_match()
	 *
	 * @param string $pattern Pattern to recusively search 
	 * into file content buffer
	 * @return array Array with matched data
	 */
	private function _search_pattern( $pattern )
	{
		$this->search = "%^{$pattern}:(.*)$%mi";
		return preg_match( 
			$this->search, 
				self::fetch_theme_contents(), 
					$this->buffer );
	}

	public function return_tags(  )
	{
		$this->_search_pattern( 'Tags' );
		$split_tags = explode( ',', $this->buffer[1] );
		
		foreach( $split_tags as $tag ) :
			$this->tags[] = $tag; 
		endforeach;

		return $this->return_to_type( 'tags' );
	}

	public function return_to_type( $property, $type='' )
	{
		$type = ( empty ( $type ) )  ? $this->type : $type;

		switch ( $type ) {
			case 'object':
				return (object) $this->$property;
				break;
			case 'json':
				return json_encode( $this->$property);
				break;
			case 'array':
				return (array) $this->$property;
				break;
			default:
				return (array) $this->$property; 
				break;
		}
	}

	public function get_everything( $type = 'array' )
	{
		$patterns = array(
			'name' 			=> 	'Theme Name',
			'theme_uri'		=>	'Theme URI',
			'author'		=>	'Author',
			'author_uri' 	=>	'Author URI',
			'version' 		=>	'Version',
			'license' 		=>	'License',
			'license_uri' 	=>	'License URI',
			'text_domain' 	=>	'Text Domain',
		);

		foreach( $patterns as $key => $pattern ) :
			$this->_search_pattern( $pattern );
			$data[$key] = $this->buffer[1];
		endforeach;

		$data['tags'] = $this->_get_property( 'tags' );

		$this->data = $data;

		return $this->return_to_type( 'data', $type );
	}

	/**
	 * Gets wanted part of style.css file
	 *
	 * @access public
	 *
	 * @param string $value The value wanted
	 * @param string $type = the type to return, options are: "object", "array", "json". Default's array.
	 * you can choose object or array to return
	 *
	 * @return string String with wanted data
	 */
	private function _get_property( $value='' )
	{
		$patterns = array(
			'name' 			=> 	'Theme Name',
			'theme_uri'		=>	'Theme URI',
			'author'		=>	'Author',
			'author_uri' 	=>	'Author URI',
			'version' 		=>	'Version',
			'license' 		=>	'License',
			'license_uri' 	=>	'License URI',
			'text_domain' 	=>	'Text Domain',
		);		

		if ( $value == 'tags' ) {
			return $this->return_tags( $this->type );
		}

		if ( $value == 'data' ) {
			return $this->get_everything();
		}

		$this->_search_pattern( $patterns[ $value ]  );
		$this->$patterns[ $value ] = $this->buffer[1];

		return $this->$patterns[ $value ];
	}

}