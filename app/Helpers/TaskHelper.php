<?php
namespace App\Helpers;

use App\Models\Api\Tasks;

class TaskHelper
{
    public static function tasklist_exist($tasklist_id)
    {
        $tasklist = Tasks::where('company_id', get_company_id())->where('deleted', 0)->findOr($tasklist_id, function () {
            return false;
        });

        return $tasklist ?? false;
    }    
}
