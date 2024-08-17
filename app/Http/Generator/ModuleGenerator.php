<?php

namespace App\Http\Generator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\generatorModels\Module;

class ModuleGenerator extends Controller
{
    public function index()
    {
        $modules = Module::all();
        return response()->json($modules);
    }

    public function show($id)
    {
        $module = Module::findOrFail($id);
        return response()->json($module);
    }

    public function store(Request $request)
    {
        $request->validate([
            'module_name' => 'required|string|max:100',
            'module_title' => 'required|string|max:100',
            'module_note' => 'nullable|string|max:255',
            'module_author' => 'nullable|string|max:100',
            'module_desc' => 'nullable|string|max:255',
            'module_db' => 'required|string|max:255',
            'module_type' => 'nullable|string|max:255',
            'module_config' => 'nullable|string|max:255',
            'module_lang' => 'nullable|string|max:255',
        ]);

        $moduleName = $request->input('module_name');
        $moduleDB = $request->input('module_db');
        $moduleDBKey = self::findPrimarykey($moduleDB);

        $data = [
            'module_name' => $moduleName,
            'module_title' => $request->input('module_title'),
            'module_note' => $request->input('module_note'),
            'module_author' => $request->input('module_author'),
            'module_desc' => $request->input('module_desc'),
            'module_db' => $moduleDB,
            'module_db_key' => $moduleDBKey,
            'module_type' => $request->input('module_type', 'native'),
            'module_config' => $request->input('module_config'),
            'module_lang' => $request->input('module_lang'),
            'module_created' => now(),
        ];

        $module = Module::create($data);

        $module = self::createModule($moduleName, $moduleDB, $moduleDBKey);
        return response()->json(['message' => 'Module created successfully', 'data' => $module], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'module_name' => 'required|string|max:100',
            'module_title' => 'required|string|max:100',
            'module_note' => 'nullable|string|max:255',
            'module_author' => 'required|string|max:100',
            'module_created' => 'nullable|date',
            'module_desc' => 'nullable|string',
            'module_db' => 'required|string|max:255',
            'module_db_key' => 'required|string|max:100',
            'module_type' => 'nullable|string|max:20',
            'module_config' => 'nullable|string',
            'module_lang' => 'nullable|string',
        ]);

        $module = Module::findOrFail($id);
        $module->update($request->all());
        return response()->json(['message' => 'Module updated successfully', 'data' => $module]);
    }

    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();
        return response()->json(['message' => 'Module deleted successfully']);
    }

    public function createModule($moduleName, $moduleDB, $moduleDBKey)
    {
        $this->generateModel($moduleName, $moduleDB, $moduleDBKey);
        $this->generateController($moduleName);
        $this->addRoute($moduleName);

        return response()->json([
            'message' => 'Module created successfully',
        ], 201);
    }


    protected function generateModel($moduleName, $moduleDB, $moduleDBKey)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($moduleDB);

        $fillableFields = "'" . implode("', '", $columns) . "'";

        $modelTemplate = <<<PHP
                        <?php

                        namespace App\Models;

                        use Illuminate\Database\Eloquent\Factories\HasFactory;
                        use Illuminate\Database\Eloquent\Model;

                        class {$moduleName} extends Model
                        {
                            use HasFactory;

                            protected \$table = '{$moduleDB}';
                            protected \$primaryKey = '{$moduleDBKey}';
                            public \$incrementing = true;
                            public \$timestamps = true;

                            protected \$fillable = [
                                {$fillableFields}
                            ];
                        }
                        PHP;

        $modelPath = app_path("Models/{$moduleName}.php");

        // if (!File::exists(app_path('Models/Sximo'))) {
        //     File::makeDirectory(app_path('Models/Sximo'));
        // }

        file_put_contents($modelPath, $modelTemplate);
    }

    protected function generateController($moduleName)
    {
        $controllerTemplate = <<<PHP
                            <?php

                            namespace App\Http\Controllers;

                            use App\Models\\{$moduleName};
                            use Illuminate\Http\Request;
                            use App\Http\Controllers\Controller;

                            class {$moduleName}Controller extends Controller
                            {
                                public function index()
                                {
                                    \$items = {$moduleName}::all();
                                    return response()->json(\$items);
                                }

                                public function store(Request \$request)
                                {
                                    \$request->validate([
                                    //    use the validation rules
                                    ]);

                                    \$item = {$moduleName}::create(\$request->all());
                                    return response()->json(['message' => '{$moduleName} created successfully', 'data' => \$item], 201);
                                }

                                public function show(\$id)
                                {
                                    \$item = {$moduleName}::findOrFail(\$id);
                                    return response()->json(\$item);
                                }

                                public function update(Request \$request, \$id)
                                {
                                    \$request->validate([
                                        //    use the validation rules
                                    ]);

                                    \$item = {$moduleName}::findOrFail(\$id);
                                    \$item->update(\$request->all());
                                    return response()->json(['message' => '{$moduleName} updated successfully', 'data' => \$item]);
                                }

                                public function destroy(\$id)
                                {
                                    \$item = {$moduleName}::findOrFail(\$id);
                                    \$item->delete();
                                    return response()->json(['message' => '{$moduleName} deleted successfully']);
                                }
                            }
                            PHP;

        $controllerPath = app_path("Http/Controllers//{$moduleName}Controller.php");

        // if (!File::exists(app_path('Http/Controllers/Sximo'))) {
        //     File::makeDirectory(app_path('Http/Controllers/Sximo'));
        // }

        file_put_contents($controllerPath, $controllerTemplate);
    }

    protected function addRoute($moduleName)
    {
        $routePath = base_path('routes/modules.php');
        $routeEntry = <<<PHP

    // This is route for {$moduleName}
    Route::apiResource('{$moduleName}', App\Http\Controllers\\{$moduleName}Controller::class);
    // End of route for {$moduleName}
    
    PHP;

        File::append($routePath, $routeEntry);
    }

    function findPrimarykey($table)
    {
        $query = "SHOW columns FROM `{$table}` WHERE extra LIKE '%auto_increment%'";
        $primaryKey = '';
        foreach (DB::select($query) as $key) {
            $primaryKey = $key->Field;
        }
        return $primaryKey;
    }
}
