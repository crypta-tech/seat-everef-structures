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

namespace CryptaTech\Seat\EverefStructures\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Exception;
use Seat\Eveapi\Mapping\Structures\UniverseStructureMapping;

/**
 * Class Update.
 *
 * @package CryptaEve\Seat\EverefStructures\Commands
 */
class Update extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'cryptatech:everef-structures:update
                            {--force : Force re-installation of an existing SDE version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update UniverseStructures from Everef structure data';

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $start = now();

        $this->info('About to fetch everef structures');
        
        $response = Http::retry(2, 500)->accept('application/json');

        if ($this->option('force') == false) {
            $response = $response->withHeaders([
                'If-None-Match' => setting('cryptatech_seat_everef_structures_etag', true)
            ]);
        }

        $response = $response->get('https://data.everef.net/structures/structures-latest.v2.json');

        $this->info('Initial Response took ' . now()->diffInSeconds($start) . ' seconds');

        if ($response->status() == 304) {
            $this->info('Already have the latest structure dump, not forcing an update');
            return $this::SUCCESS;    
        }

        if(!$response->ok()){
            $this->error('Got a bad response code! -> ' . $response->status());
            dd($response); // This is a bad debugging hack lol 
        }
        $this->info('ETag: ' . $response->header('ETag'));

        // Save the ETag for future runs
        setting(['cryptatech_seat_everef_structures_etag', $response->header('ETag')], true);

        $structures = $response->json();

        // Now have a good json response so start parsing preparing to put into database :)
        $this->info('Got ' . count($structures) . ' potential structures. Beginning import.');

        $bar = $this->output->createProgressBar(count($structures));
        $bar->start();

        $imported = 0;

        foreach ($structures as $structure_id => $structure) {

            // Validate that we have all the required fields set
            if (!(
                isset($structure["name"]) &&
                isset($structure["owner_id"]) &&
                isset($structure["position"]) &&
                isset($structure["position"]["x"]) &&
                isset($structure["position"]["y"]) &&
                isset($structure["position"]["z"]) &&
                isset($structure["solar_system_id"]) &&
                isset($structure["type_id"])
            )){
                $bar->advance();
                continue;
            }

            $model = UniverseStructure::firstOrNew([
                'structure_id' => $structure_id,
            ]);

            if ($model->exists) {
                $bar->advance();
                continue;
            }

            try {
                UniverseStructureMapping::make($model, $structure, [
                    'structure_id' => function () use ($structure_id) {
                        return $structure_id;
                    },
                ])->save();
                $imported += 1;
            } catch (Exception $e) {
                dd($structure_id, $structure, $e);
            }
            
            $bar->advance();
            // dd($m);

        }

        $bar->finish();
        $this->line('');
        $this->info('Import Complete! ' . $imported . ' structures added');


        return $this::SUCCESS;
    }
}
