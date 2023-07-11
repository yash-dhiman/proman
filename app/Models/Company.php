<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table        = 'company';
    protected $primaryKey   = 'company_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable     = [
                                    'company_name', 
                                    'company_url', 
                                    'address',
                                    'registared_at'
                                ];
   

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts        = [
                                    'registared_at' => 'datetime'
                                ];
}
