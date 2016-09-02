<?php
/**
 * This file is part of the Petty TcpClient package.
 * Copyright (C) 2016 pengzhile <pengzhile@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Petty\TcpClient;

use Petty\TcpClient\Common\Helper;
use Petty\TcpClient\Interfaces\IClient;

abstract class Client implements IClient
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var bool
     */
    protected $ssl = false;

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * @var string
     */
    protected $remoteHost;

    /**
     * @var string
     */
    protected $remoteIp;

    /**
     * @var int
     */
    protected $remotePort;

    /**
     * @var array
     */
    protected $sslOptions = array(
        'disable_compression' => true
    );

    /**
     * @var array
     */
    protected $tcpOptions = array();

    /**
     * @return bool
     */
    public function close()
    {
        if (!$this->connected) {
            return true;
        }

        $ret = fclose($this->stream);
        $this->stream = null;
        $this->connected = false;

        return $ret;
    }

    /**
     * @param bool $enable
     */
    public function useSSL($enable)
    {
        $this->ssl = $enable;
    }

    /**
     * @param array $options
     */
    public function setSSLOption(array $options)
    {
        $this->sslOptions = $options + $this->sslOptions;
    }

    /**
     * @param array $options
     */
    public function setTCPOption(array $options)
    {
        $this->tcpOptions = $options + $this->tcpOptions;
    }

    /**
     * @return boolean
     */
    public function isSSL()
    {
        return $this->ssl;
    }

    /**
     * @return boolean
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @return string
     */
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }

    /**
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }

    /**
     * @return int
     */
    public function getRemotePort()
    {
        return $this->remotePort;
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $timeout milliseconds
     * @param bool $async
     * @return resource|false
     */
    protected function newConnection($host, $port, $timeout, $async)
    {
        if ($this->connected) {
            return false;
        }

        $ip = Helper::host2ip($host);
        $flag = $async ? STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT : STREAM_CLIENT_CONNECT;

        $this->remoteHost = $host;
        $this->remoteIp = $ip;
        $this->remotePort = $port;

        if ($this->ssl) {
            $remote = 'tls://' . $ip . ':' . $port;
            $options['ssl'] = $this->sslOptions;
        } else {
            $remote = 'tcp://' . $ip . ':' . $port;
            $options['socket'] = $this->tcpOptions;
        }

        $context = stream_context_create($options);
        $fp = stream_socket_client($remote, $errNo, $errStr, $timeout / 1000, $flag, $context);
        $this->connected = (bool)$fp;

        return $fp;
    }
}
