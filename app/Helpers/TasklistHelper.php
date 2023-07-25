<?php
namespace App\Helpers;

use App\Models\Api\Tasklists;

class TasklistHelper
{
    public static function tasklist_exist($tasklist_id)
    {
        $tasklist = Tasklists::where('company_id', get_company_id())->where('deleted', 0)->findOr($tasklist_id, function () {
            return false;
        });

        return $tasklist ?? false;
    }    
}
