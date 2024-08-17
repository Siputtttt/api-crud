<?php

namespace App\Models\generatorModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'tb_module';
    protected $primaryKey = 'module_id';

    protected $fillable = [
        'module_name',
        'module_title',
        'module_note',
        'module_author',
        'module_created',
        'module_desc',
        'module_db',
        'module_db_key',
        'module_type',
        'module_config',
        'module_lang'
    ];
}
