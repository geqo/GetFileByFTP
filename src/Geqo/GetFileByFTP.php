<?php
/*
 *
 * Copyright © 2018 Alex White geqo.ru
 * Author: Alex White
 * All rights reserved
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace Geqo;

use ErrorException;
use Geqo\Exceptions\FTPException;

class GetFileByFTP
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var resource
     */
    private $connection;

    /**
     * Enable or disable passive mode
     * @var bool
     */
    private $passive = false;

    /**
     * Connection encoding
     * @var string
     */
    private $encoding = 'UTF8';

    /**
     * GetFileByFTP constructor.
     * @param $host
     * @param int $port
     * @param $user
     * @param $pass
     * @throws FTPException
     */
    public function __construct($host, $user, $pass, $port = 21, $timeout = 15)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->timeout = $timeout;

        $this->connect();
        $this->login();
    }

    /**
     * Passive mode, default false.
     * @param bool $passive
     * @throws FTPException
     */
    public function setPassive(bool $passive)
    {
        $this->passive = $passive;

        $try = ftp_pasv($this->connection, $this->passive);

        if (! $try) {
            throw new FTPException('Cannot set passive mode to ' . ($try ? '`true`' : '`false`') . '.');
        }
    }

    /**
     * @param string $encoding
     */
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;
        ftp_raw($this->connection, 'OPTS ' . $encoding . ' ON');
    }

    /**
     * Connect to server
     * @throws FTPException
     */
    private function connect()
    {
        $conn = ftp_connect($this->host, $this->port, $this->timeout);

        if (! $conn) {
            throw new FTPException('Connection error.');
        }

        $this->connection = $conn;
    }

    /**
     * @throws FTPException
     */
    private function login()
    {
        if (! ftp_login($this->connection, $this->user, $this->pass)) {
            throw new FTPException('Authentication error');
        }
    }

    /**
     * Download file
     * @param $from
     * @param $to
     * @throws FTPException
     */
    public function getFile($from, $to)
    {
        $pathinfo = pathinfo($to);

        if (! is_writable($pathinfo['dirname'])) {
            throw new FTPException('Target directory is not writable');
        }

        set_error_handler(function($errno, $errstr, $errfile, $errline){
	        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_WARNING);

        $result = ftp_get($this->connection, $to, $from, FTP_BINARY);

	    restore_error_handler();

        if (! $result) {
            throw new FTPException('File not found or not written.');
        }
    }
}