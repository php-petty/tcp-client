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

use Petty\TcpClient\SyncClient;
use PHPUnit_Framework_TestCase;

class SyncClientTest extends PHPUnit_Framework_TestCase
{
    public function testSSL()
    {
        $oldER = error_reporting(-1);

        $host = 'github.com';

        $client = new SyncClient();

        $client->useSSL(true);
        $client->setSSLOption(array(
            'peer_name' => $host,
            'disable_compression' => true,
            'SNI_enabled' => true,
            'cafile' => __DIR__ . '/../src/Common/cacert.pem',
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

        $this->assertTrue($client->connect($host, 443));

        $data = "GET / HTTP/1.1\r\nHost: {$host}\r\nConnection: close\r\n\r\n";
        $len = $client->send($data);
        $this->assertEquals($len, strlen($data));

        $timeout = 30000;
        $delta = microtime(true);
        $ret = $client->receive($timeout);
        $delta = microtime(true) - $delta;

        $this->assertTrue($client->close());
        $this->assertTrue($delta * 1000 <= $timeout);
        $this->assertNotEmpty($ret);

        error_reporting($oldER);
    }

    public function testTCP()
    {
        $oldER = error_reporting(-1);

        $host = 'www.apple.com';

        $client = new SyncClient();

        $this->assertTrue($client->connect($host, 80));

        $data = "GET / HTTP/1.1\r\nHost: {$host}\r\nConnection: close\r\n\r\n";
        $len = $client->send($data);
        $this->assertEquals($len, strlen($data));

        $timeout = 30000;
        $delta = microtime(true);
        $ret = $client->receive($timeout);
        $delta = microtime(true) - $delta;

        $this->assertTrue($client->close());
        $this->assertTrue($delta * 1000 <= $timeout);
        $this->assertNotEmpty($ret);

        error_reporting($oldER);
    }
}
