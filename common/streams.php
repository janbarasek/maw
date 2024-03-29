<?php
/*
   Copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.

   This file is part of PHP-gettext.

   PHP-gettext is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   PHP-gettext is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with PHP-gettext; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/


class StreamReader
{

	function read($bytes)
	{
		return false;
	}

	function seekto($position)
	{
		return false;
	}

	function currentpos()
	{
		return false;
	}

	function length()
	{
		return false;
	}
}

class StringReader
{
	var $_pos;
	var $_str;

	function StringReader($str = '')
	{
		$this->_str = $str;
		$this->_pos = 0;
	}

	function read($bytes)
	{
		$data = substr($this->_str, $this->_pos, $bytes);
		$this->_pos += $bytes;
		if (strlen($this->_str) < $this->_pos)
			$this->_pos = strlen($this->_str);

		return $data;
	}

	function seekto($pos)
	{
		$this->_pos = $pos;
		if (strlen($this->_str) < $this->_pos)
			$this->_pos = strlen($this->_str);

		return $this->_pos;
	}

	function currentpos()
	{
		return $this->_pos;
	}

	function length()
	{
		return strlen($this->_str);
	}

}


class FileReader
{
	var $_pos;
	var $_fd;
	var $_length;

	function FileReader($filename)
	{
		if (file_exists($filename)) {

			$this->_length = filesize($filename);
			$this->_pos = 0;
			$this->_fd = fopen($filename, 'rb');
			if (!$this->_fd) {
				$this->error = 3; // Cannot read file, probably permissions

				return false;
			}
		} else {
			$this->error = 2; // File doesn't exist

			return false;
		}
	}

	function read($bytes)
	{
		if ($bytes) {
			fseek($this->_fd, $this->_pos);


			while ($bytes > 0) {
				$chunk = fread($this->_fd, $bytes);
				$data .= $chunk;
				$bytes -= strlen($chunk);
			}
			$this->_pos = ftell($this->_fd);

			return $data;
		} else return '';
	}

	function seekto($pos)
	{
		fseek($this->_fd, $pos);
		$this->_pos = ftell($this->_fd);

		return $this->_pos;
	}

	function currentpos()
	{
		return $this->_pos;
	}

	function length()
	{
		return $this->_length;
	}

	function close()
	{
		fclose($this->_fd);
	}

}


class CachedFileReader extends StringReader
{
	function CachedFileReader($filename)
	{
		if (file_exists($filename)) {

			$length = filesize($filename);
			$fd = fopen($filename, 'rb');

			if (!$fd) {
				$this->error = 3; // Cannot read file, probably permissions

				return false;
			}
			$this->_str = fread($fd, $length);
			fclose($fd);

		} else {
			$this->error = 2; // File doesn't exist

			return false;
		}
	}
}


?>