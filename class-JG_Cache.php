<?php

// no direct access
defined( 'ABSPATH' ) or die( 'Restricted Access' );

class chimpexpressJG_Cache {

    function __construct($dir, $useFTP = false, $handler = NULL)
    {
        $this->dir = $dir;
	$this->useFTP = $useFTP;
	$this->handler = $handler;
        
    }

    private function _name($key)
    {
        return sprintf("%s/%s", $this->dir, sha1($key));
    }

    public function get($key, $expiration = 0)
    {
        if ( !is_dir(ABSPATH . $this->dir) )
        {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if (!@file_exists(ABSPATH . $cache_path))
        {
            return FALSE;
        }

        if ( $expiration > 0 && filemtime(ABSPATH . $cache_path) < (time() - $expiration) )
        {
            $this->clear($key);
            return FALSE;
        }

        if (!$fp = @fopen(ABSPATH . $cache_path, 'rb'))
        {
            return FALSE;
        }

        flock($fp, LOCK_SH);

        $cache = '';

        if (filesize(ABSPATH . $cache_path) > 0)
        {
            $cache = unserialize(fread($fp, filesize(ABSPATH . $cache_path)));
        }
        else
        {
            $cache = NULL;
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $cache;
    }

    public function set($key, $data)
    {
        if ( !is_dir( ABSPATH . $this->dir) )
        {
            return FALSE;
        }

        $cache_path = $this->_name($key);

	if( $this->useFTP ) {
	    $temp = tmpfile();
	    fwrite($temp, serialize($data));
	    rewind($temp);
	    if ( ! @ftp_fput($this->handler, $cache_path, $temp, FTP_ASCII) )
	    {
		return FALSE;
	    }
	} else {
	    
	    if ( !$this->handler->is_writable( ABSPATH . $this->dir ) )
	    {
		return FALSE;
	    }

	    if ( ! $this->handler->put_contents( ABSPATH . $cache_path, serialize($data) ) )
	    {
		return FALSE;
	    }
	}

        return TRUE;
    }

    public function clear($key)
    {
        $cache_path = $this->_name($key);

        if (file_exists(ABSPATH . $cache_path))
        {
            if( $this->useFTP ){
		@ftp_delete($this->handler, $cache_path);
	    } else {
		$this->handler->delete( $cache_path );
	    }
            return TRUE;
        }

        return FALSE;
    }
}
