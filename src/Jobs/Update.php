<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

namespace CryptaEve\Seat\EverefStructures\Commands;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class Maintenance.
 *
 * @package CryptaEve\Seat\EverefStructures\Commands
 */
class Update implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum duration of job in seconds before being considered as dead.
     *
     * @var int
     */
    public $timeout = 12000;

    /**
     * The maximum duration of job in seconds before being retried.
     *
     * @var int
     */
    public $backoff = 12001;

    /**
     * Perform the maintenance job.
     */
    public function handle()
    {

        $response = Http::retry(2, 500)->get('https://data.everef.net/structures/structures-latest.v2.json');

        if(!$response->ok()){
            throw new Exception("Failed to make request", $response->status());
        }

        // Now have a good json response so start parsing preparing to put into database :)
        
        
    }
}