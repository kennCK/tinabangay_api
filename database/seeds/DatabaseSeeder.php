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
        $dt = Carbon::now();
        $dateNow = $dt->toDateTimeString();
        //  DB:: table('modules') -> insert(array(
        //   array('id' => 1, 'parent_id' => 0,  'description'=>'Dashboard', 'icon' => 'fa fa-tachometer', 'path' => 'dashboard', 'rank' => 1),
        //   array('id' => 2, 'parent_id' => 0,  'description'=>'Semester', 'icon' => 'fa fa-sitemap', 'path' => 'semester', 'rank' => 2),
        //   array('id' => 3, 'parent_id' => 0,  'description'=>'Courses', 'icon' => 'fa fa-clipboard',  'path' => 'courses/default', 'rank' => 3),
        //   array('id' => 4, 'parent_id' => 0,  'description'=>'Quizzes', 'icon' => 'fa fa-file-text-o',  'path' => 'quizzes/default', 'rank' => 4),
        //   array('id' => 5, 'parent_id' => 0,  'description'=>'Exams', 'icon' => 'fa fa-file-text-o',  'path' => 'exams/default', 'rank' => 5),
        //   array('id' => 6, 'parent_id' => 0,  'description'=>'Resources', 'icon' => 'fa fa-file-text-o',  'path' => 'resources/default', 'rank' => 6),
        //   array('id' => 10, 'parent_id' => 0,  'description'=>'My Account', 'icon' => 'fa fa-cog',  'path' => 'account_settings', 'rank' => 10)
        // ));


        // DB:: table('rides')->insert(array(
        //     array('id'=>1, 'account_id'=>1, 'payload'=>'1', 'owner'=>1, 'transportation_id'=>'123', 'from' => 'talamban', 'from_date_time'=>$dateNow, 'to'=>'colon', 'to_date_time'=>$dateNow, 'type'=>'type1', 'code'=>'code1'),
        //     array('id'=>2, 'account_id'=>2, 'payload'=>'2', 'owner'=>2, 'transportation_id'=>'345', 'from' => 'talamban', 'from_date_time'=>$dateNow, 'to'=>'colon', 'to_date_time'=>$dateNow, 'type'=>'type1', 'code'=>'code1'),
        //     array('id'=>3, 'account_id'=>4, 'payload'=>'3', 'owner'=>3, 'transportation_id'=>'567', 'from' => 'talamban', 'from_date_time'=>$dateNow, 'to'=>'colon', 'to_date_time'=>$dateNow, 'type'=>'type1', 'code'=>'code1'),

        // ));

        // DB:: table('patients')->insert(array(
        //     array('id'=>1, 'account_id'=>1, 'added_by'=>1, 'status'=>'positive'),
        //     array('id'=>2, 'account_id'=>2, 'added_by'=>2, 'status'=>'negative'),
        //     array('id'=>3, 'account_id'=>3, 'added_by'=>3, 'status'=>'PUI'),
        // ));

        //  DB:: table('temperatures')->insert(array(
        //     array('id'=>1, 'account_id'=>1, 'added_by'=>1, 'value'=>36.2, 'remarks'=>'good'),
        //     array('id'=>2, 'account_id'=>2, 'added_by'=>2, 'value'=>35.2, 'remarks'=>'good'),
        //     array('id'=>3, 'account_id'=>3, 'added_by'=>3, 'value'=>34.2, 'remarks'=>'good'),
        // ));
        
        // DB:: table('temperature_locations')->insert(array(
        //     array('id'=>1, 'account_id'=>1, 'temperature_id'=>'1', 'longitude'=>'1', 'latitude'=>'1', 'route' => 'colon-madawe', 'locality'=>'colon', 'country'=>'phil', 'region'=>'8'),
        //     array('id'=>2, 'account_id'=>2, 'temperature_id'=>'2', 'longitude'=>'1', 'latitude'=>'1', 'route' => 'colon-madawe', 'locality'=>'colon', 'country'=>'phil', 'region'=>'8'),
        //     array('id'=>3, 'account_id'=>3, 'temperature_id'=>'3', 'longitude'=>'1', 'latitude'=>'1', 'route' => 'colon-madawe', 'locality'=>'colon', 'country'=>'phil', 'region'=>'8'),
        // ));
    }
}
