<?php

namespace App\Models\api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;
    protected $primaryKey   = 'file_id';

    protected $fillable     = [
                                'company_id',
                                'project_id',
                                'file_name',
                                'file_real_name',
                                'file_type',
                                'related_to',
                                'related_to_id',
                                'file_version',
                                'file_size',
                                'file_extension',
                                'pages',
                                // 'created_at',
                                // 'created_by',
                                // 'updated_by',
                                // 'updated_at'
                            ];
}
