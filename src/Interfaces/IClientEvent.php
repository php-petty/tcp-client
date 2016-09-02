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

use Exception;

interface IClientEvent
{
    /**
     * @param IClient $client
     * @return void
     */
    public function onConnect(IClient $client);

    /**
     * @param IClient $client
     * @return void
     */
    public function onConnected(IClient $client);

    /**
     * @param IClient $client
     * @param string $data
     * @return void
     */
    public function onStream(IClient $client, $data);

    /**
     * @param IClient $client
     * @return void
     */
    public function onClosed(IClient $client);

    /**
     * @param IClient $client
     * @param Exception $exception
     * @return void
     */
    public function onError(IClient $client, Exception $exception);
}
