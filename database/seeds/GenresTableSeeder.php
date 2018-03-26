<?php

use App\Genre;
use Illuminate\Database\Seeder;

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Genre::create(['genre' => 'Biographies & Memoirs']);
        Genre::create(['genre' => 'Business & Investing']);
        Genre::create(['genre' => 'Children’s Books']);
        Genre::create(['genre' => 'Computers & Technology']);
        Genre::create(['genre' => 'Fiction & Literature']);
        Genre::create(['genre' => 'Health & Sports']);
        Genre::create(['genre' => 'History']);
        Genre::create(['genre' => 'Mystery & Thrillers']);
        Genre::create(['genre' => 'Romance']);
        Genre::create(['genre' => 'Science Fiction & Fantasy']);
        Genre::create(['genre' => 'Other']);
    }
}
