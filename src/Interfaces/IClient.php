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

namespace Petty\TcpClient\Interfaces;

interface IClient
{
    /**
     * @param string $host
     * @param int $port
     * @param int $timeout milliseconds
     * @return bool
     */
    public function connect($host, $port, $timeout);

    /**
     * @return bool
     */
    public function isConnected();

    /**
     * @param string $data
     * @return int
     */
    public function send($data);

    /**
     * @return bool
     */
    public function close();

    /**
     * @return string
     */
    public function getRemoteHost();

    /**
     * @return string
     */
    public function getRemoteIp();

    /**
     * @return int
     */
    public function getRemotePort();
}
