<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  DB:: table('modules') -> insert(array(
        //   array('id' => 1, 'parent_id' => 0,  'description'=>'Dashboard', 'icon' => 'fa fa-tachometer', 'path' => 'dashboard', 'rank' => 1),
        //   array('id' => 2, 'parent_id' => 0,  'description'=>'Semester', 'icon' => 'fa fa-sitemap', 'path' => 'semester', 'rank' => 2),
        //   array('id' => 3, 'parent_id' => 0,  'description'=>'Courses', 'icon' => 'fa fa-clipboard',  'path' => 'courses/default', 'rank' => 3),
        //   array('id' => 4, 'parent_id' => 0,  'description'=>'Quizzes', 'icon' => 'fa fa-file-text-o',  'path' => 'quizzes/default', 'rank' => 4),
        //   array('id' => 5, 'parent_id' => 0,  'description'=>'Exams', 'icon' => 'fa fa-file-text-o',  'path' => 'exams/default', 'rank' => 5),
        //   array('id' => 6, 'parent_id' => 0,  'description'=>'Resources', 'icon' => 'fa fa-file-text-o',  'path' => 'resources/default', 'rank' => 6),
        //   array('id' => 10, 'parent_id' => 0,  'description'=>'My Account', 'icon' => 'fa fa-cog',  'path' => 'account_settings', 'rank' => 10)
        // ));

        // DB:: table('account_informations') -> insert(array(
        //     array('id' => 9, 'account_id' => 6, 'first_name'=>'patrick', 'middle_name' => 'lolo', 'last_name' => 'cabia-an', 'birth_date' => '2000-01-01', 'sex' => 'male', 'cellular_number' => '09484862323', 'address'=> 'Talamban'), 
        //     array('id' => 10, 'account_id' => 4, 'first_name'=>'leonilo', 'middle_name' => 'yol', 'last_name' => 'torres', 'birth_date' => '2000-03-01', 'sex' => 'male', 'cellular_number' => '09484862325', 'address'=> 'Talamban'),
        //     array('id' => 4, 'account_id' => 5, 'first_name'=>'renan', 'middle_name' => 'cañete', 'last_name' => 'bargaso', 'birth_date' => '2000-04-01', 'sex' => 'male', 'cellular_number' => '09484862322', 'address'=> 'Talamban')
        // ));

        // DB:: table('patients') -> insert(array(
        //     array('id' => 1, 'account_id' => 6, 'added_by'=> 3, 'status' => 'negative'),
        //     array('id' => 2, 'account_id' => 4, 'added_by'=> 3, 'status' => 'negative'),
        //     array('id' => 3, 'account_id' => 5, 'added_by'=> 3, 'status' => 'negative'),
        // ));

        DB:: table('visited_places') -> insert(array(
            array('id' => 1, 'account_id' => 6, 'longitude'=>90.3, 'latitude'=>40.6, 'route'=>'cebu', 'locality'=>'cebu', 'country'=>'phil', 'region'=>'8', 'date'=> '2000-01-01', 'time'=> Carbon::now()),
            array('id' => 2, 'account_id' => 5, 'longitude'=>90.3, 'latitude'=>40.6, 'route'=>'cebu', 'locality'=>'cebu', 'country'=>'phil', 'region'=>'8', 'date'=> '2000-01-01', 'time'=> Carbon::now()),
            array('id' => 3, 'account_id' => 4, 'longitude'=>90.3, 'latitude'=>40.6, 'route'=>'cebu', 'locality'=>'cebu', 'country'=>'phil', 'region'=>'8', 'date'=> '2000-01-01', 'time'=> Carbon::now()),
        ));
    }
}
