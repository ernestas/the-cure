<?php
/**
 * A connection interface
 *
 * @package     TheCure
 * @category    Connection
 * @copyright   Gignite, 2012
 * @license     MIT
 */
namespace TheCure\Connections;

interface Connection {

	/**
	 * @abstract
	 * @return mixed
	 */
	public function get();

}