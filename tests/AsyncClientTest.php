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

namespace Petty\TcpClient\Tests;

use Petty\TcpClient\AsyncClient;
use PHPUnit_Framework_TestCase;

class AsyncClientTest extends PHPUnit_Framework_TestCase
{
    public function testSSL()
    {
        $oldER = error_reporting(-1);

        $host = 'github.com';

        $client = new AsyncClient(new ClientEvent($this));

        $client->useSSL(true);
        $client->setSSLOption(array(
            'peer_name' => $host,
            'disable_compression' => true,
            'SNI_enabled' => true,
            'cafile' => realpath(__DIR__ . '/../src/Common/cacert.pem'),
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
            'verify_depth' => 7,
        ));

        if (PHP_VERSION_ID < 50600) {
            $client->setSSLOption(array(
                'CN_match' => $host,
                'SNI_server_name' => $host
            ));
        }

        $client->connect($host, 443);

        error_reporting($oldER);
    }

    public function testTCP()
    {
        $oldER = error_reporting(-1);

        $host = 'www.apple.com';

        $client = new AsyncClient(new ClientEvent($this));

        $client->connect($host, 80);

        error_reporting($oldER);
    }
}
