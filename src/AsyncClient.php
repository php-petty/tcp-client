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

use BadMethodCallException;
use ErrorException;
use Exception;
use Petty\TcpClient\Common\Event;
use Petty\TcpClient\Exceptions\ConnectFailed;
use Petty\TcpClient\Exceptions\ConnectTimeout;
use Petty\TcpClient\Exceptions\ReceiveTimeout;
use Petty\TcpClient\Interfaces\IClientEvent;

class AsyncClient extends Client
{
    /**
     * @var IClientEvent
     */
    protected $clientEvent;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var string
     */
    protected $sendBuffer;

    /**
     * @var int
     */
    protected $receiveTimeout = 30000;

    public function __construct(IClientEvent $event)
    {
        $this->clientEvent = $event;

        set_error_handler(function ($code, $msg, $file, $line) {
            throw new ErrorException($msg, $code, $code, $file, $line);
        }, error_reporting());

        set_exception_handler(array($this, 'exceptionOccurred'));
    }

    public function send($data)
    {
        if ($this->sendBuffer) {
            $this->sendBuffer .= $data;

            return 0;
        }

        $len = fwrite($this->stream, $data);
        if (false === $len) {
            $this->close();

            return false;
        }

        if (strlen($data) > $len) {
            $this->sendBuffer = substr($data, 0, $len);
            $this->event->onWrite($this->stream, array($this, 'writeBufferCallback'), null, $this->receiveTimeout);
        }

        return $len;
    }

    public function connect($host, $port, $timeout = 30000)
    {
        $this->clientEvent->onConnect($this);
        $this->stream = $this->newConnection($host, $port, $timeout, true);

        if (false === $this->stream) {
            throw new ConnectFailed();
        }

        stream_set_blocking($this->stream, 0);

        $this->sendBuffer = '';
        $this->event = new Event();
        $this->event->onWrite($this->stream, array($this, 'connectedCallback'), null, $timeout);
        $this->event->loop();

        return true;
    }

    public function close()
    {
        $this->event->stop();
        $ret = parent::close();
        $this->clientEvent->onClosed($this);

        return $ret;
    }

    /**
     * @return int
     */
    public function getReceiveTimeout()
    {
        return $this->receiveTimeout;
    }

    /**
     * @param int $receiveTimeout
     */
    public function setReceiveTimeout($receiveTimeout)
    {
        $this->receiveTimeout = $receiveTimeout;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (!method_exists($this, $name)) {
            throw new BadMethodCallException();
        }

        switch (count($arguments)) {
            case 0:
                return $this->$name();
            case 1:
                return $this->$name($arguments[0]);
            case 2:
                return $this->$name($arguments[0], $arguments[1]);
            case 3:
                return $this->$name($arguments[0], $arguments[1], $arguments[2]);
            case 4:
                return $this->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
            default:
                return call_user_func_array(array($this, $name), $arguments);
        }
    }

    protected function exceptionOccurred(Exception $e)
    {
        $this->clientEvent->onError($this, $e);
    }

    protected function connectedCallback($resource, $event, array $data)
    {
        list($eventId) = $data;
        if (Event::TIMEOUT === $event) {
            $this->close();
            throw new ConnectTimeout();
        }

        $this->clientEvent->onConnected($this);
        $this->event->onRead($resource, array($this, 'receiveCallback'), null, $this->receiveTimeout);
        $this->event->remove($eventId);
    }

    protected function receiveCallback($resource, $event, array $data)
    {
        list($eventId) = $data;
        if (Event::TIMEOUT === $event) {
            $this->close();
            throw new ReceiveTimeout();
        }

        $first = true;
        $buffer = '';
        do {
            $ret = fread($resource, 65535);
            if ('' === $ret || false === $ret) {
                if ($first) {
                    $this->event->remove($eventId);
                    $this->close();

                    return;
                }
                break;
            }

            $first = false;
            $buffer .= $ret;
        } while (true);

        $this->clientEvent->onStream($this, $buffer);
    }

    protected function writeBufferCallback($resource, $event, array $data)
    {
        list($eventId) = $data;
        $len = fwrite($resource, $this->sendBuffer);
        if (false === $len) {
            $this->event->remove($eventId);
            $this->close();

            return;
        }

        if (strlen($this->sendBuffer) === $len) {
            $this->event->remove($eventId);
            $this->sendBuffer = '';

            return;
        }

        $this->sendBuffer = substr($this->sendBuffer, 0, $len);
    }
}
