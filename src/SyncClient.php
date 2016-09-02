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

class SyncClient extends Client
{
    public function connect($host, $port, $timeout = 30000)
    {
        $this->stream = $this->newConnection($host, $port, $timeout, false);

        return false === $this->stream ? false : true;
    }

    public function send($data)
    {
        return $this->connected ? fwrite($this->stream, $data) : false;
    }

    /**
     * @param int $timeout milliseconds
     * @param int $length
     * @return string
     */
    public function receive($timeout = 30000, $length = 65535)
    {
        if (!$this->connected) {
            return false;
        }

        $w = $e = null;
        $r = array($this->stream);
        if (!$num = stream_select($r, $w, $e, 0, $timeout * 1000)) {
            return false;
        }

        $ret = fread($this->stream, $length);
        if ('' === $ret || false === $ret) {
            $this->close();
        }

        return $ret;
    }
}
