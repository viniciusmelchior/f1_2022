<?php

namespace Database\Seeders;

use App\Models\Site\Pais;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        /**Listagem padrão de países */
        /* $pais = new Pais();
        $pais->des_nome = 'Brasil';
        $pais->user_id = 2;
        $pais->save();

        $pais = new Pais();
        $pais->des_nome = 'Alemanha';
        $pais->user_id = 2;
        $pais->save();

        $pais = new Pais();
        $pais->des_nome = 'Inglaterra';
        $pais->user_id = 2;
        $pais->save();

        $pais = new Pais();
        $pais->des_nome = 'Austria';
        $pais->user_id = 2;
        $pais->save();

        $pais = new Pais();
        $pais->des_nome = 'Holanda';
        $pais->user_id = 2;
        $pais->save(); */

        $pais = new Pais();
        $pais->des_nome = 'Bélgica';
        $pais->user_id = 2;
        $pais->save();
    }
}
