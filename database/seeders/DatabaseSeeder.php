<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Apps\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('units')->insert([
            'name'=>'APT 101',
            'id_owner'=>1
        ]);
        DB::table('units')->insert([
            'name'=>'APT 102',
            'id_owner'=>1
        ]);
        DB::table('units')->insert([
            'name'=>'APT 103',
            'id_owner'=>'0'
        ]);
        DB::table('units')->insert([
            'name'=>'APT 104',
            'id_owner'=>'0'
        ]);
        
        //areas comuns
        DB::table('areas')->insert([
            'allowed'=>'1',
            'title'=>'Academia',
            'cover'=>'gym.jpg',
            'days'=>'1,2,3,4,5',
            'start_time'=>'06:00:00',
            'end_time'=>'22:00:00',
            'datecreated'=>'2021-01-27 13:00:00'
            
        ]);
        DB::table('areas')->insert([
            'allowed'=>'1',
            'title'=>'Piscina',
            'cover'=>'pool.jpg',
            'days'=>'1,2,3,4,5',
            'start_time'=>'09:00:00',
            'end_time'=>'23:00:00',
            'datecreated'=>'2021-01-27 13:00:00'
        ]);
        DB::table('areas')->insert([
            'allowed'=>'1',
            'title'=>'Churrasqueira',
            'cover'=>'bbq.jpg',
            'days'=>'0,4,5,6',
            'start_time'=>'09:00:00',
            'end_time'=>'23:00:00',
            'datecreated'=>'2021-01-27 13:00:00'
        ]);

        //avisos mural
        DB::table('walls')->insert([
            'title'=>'A churrasqueira já esta disponivel',
            'body'=>'Reserve sua vaga no nosso app.',
            'datecreated'=>'2021-01-27 13:00:00'
        ]);
        DB::table('walls')->insert([
            'title'=>'A academia já esta disponivel',
            'body'=>'Reserve sua vaga no nosso app.',
            'datecreated'=>'2021-01-27 14:00:00'
        ]);

    }
}
