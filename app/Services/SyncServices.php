<?php

namespace App\Services;

use File;
use Str;
use Cache;
use App\Models\Modules;

class SyncServices extends BaseService
{
    protected $modulesDir;

    protected $websiteModulesDir;

    protected $landingModulesDir;

    protected $blogModulesDir;

    public function __construct()
    {
        $this->modulesDir        = storage_path('templates/launchpad-starter-v2/sr/sr-templates/');
        $this->websiteModulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/website-pages/');
        $this->landingModulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/landing-pages/');
        $this->blogModulesDir    = storage_path('templates/launchpad-starter-v2/sr/sr-templates/blog/');
        $this->systemModulesDir  = storage_path('templates/launchpad-starter-v2/sr/sr-templates/system/');
    }

    public function syncModules()
    {
        $localModules   = [];
        $groupModules   = [];
        $websiteModules = $this->getWebsitePagesJson();
        $landingModules = $this->getLandingPagesJson();
        $blogModules    = $this->getBlogPagesJson();
        $systemModules  = $this->getSystemPagesJson();

        if (is_dir($this->modulesDir)) {
            $files = preg_grep('~\.(json)$~', scandir($this->modulesDir));

            foreach ($files as $file) {
                if (strpos($file, 'json') !== false) {
                    $json_file = file_get_contents($this->modulesDir . $file);
                    $json = json_decode($json_file, true);

                    if (@$json['custom_module_group']) {
                        array_push($groupModules, $json);
                    } else {
                        $localModules = $this->localModuleMapping($localModules, $json);
                    }
                }
            }
        }

        Modules::truncate();

        foreach ($groupModules as $key => $value) {
            $value['is_free'] = $this->checkContainFreeModules(json_encode($value['custom_module_group']));

            Modules::create([
                'name' => $value['custom_module_group']['name'],
                'type' => 'group-modules',
                'modules' => json_encode($value)
            ]);
        }

        foreach ($localModules as $key => $value) {
            $preview = $this->modulesDir . 'sr-assets/module-previews/' . Str::slug($value['custom_module']['name'], '-') . '.jpg';
            if (file_exists($preview)) {
                File::copy($preview, public_path('/module-previews/') . Str::slug($value['custom_module']['name'], '-') . '.jpg');
            }

            Modules::create([
                'name' => $value['custom_module']['name'],
                'type' => 'modules',
                'modules' => json_encode($value)
            ]);
        }

        foreach ($websiteModules as $key => $value) {
            if (file_exists($value['preview'])) {
                File::copy($value['preview'], public_path('/module-previews/') . Str::slug($value['name'], '-') . '.jpg');
            }

            $value['is_free'] = $this->checkContainFreeModules(json_encode($value));

            Modules::create([
                'name' => $value['name'],
                'type' => 'website-page',
                'modules' => json_encode($value)
            ]);
        }

        foreach ($landingModules as $key => $value) {
            if (file_exists($value['preview'])) {
                File::copy($value['preview'], public_path('/module-previews/') . Str::slug($value['name'], '-') . '.jpg');
            }

            $value['is_free'] = $this->checkContainFreeModules(json_encode($value));

            Modules::create([
                'name' => $value['name'],
                'type' => 'landing-page',
                'modules' => json_encode($value)
            ]);
        }

        foreach ($blogModules as $key => $value) {
            if (file_exists($value['preview'])) {
                File::copy($value['preview'], public_path('/module-previews/') . Str::slug($value['name'], '-') . '.jpg');
            }

            Modules::create([
                'name' => $value['name'],
                'type' => 'blog',
                'modules' => json_encode($value)
            ]);
        }

        foreach ($systemModules as $key => $value) {
            if (file_exists($value['preview'])) {
                File::copy($value['preview'], public_path('/module-previews/') . Str::slug($value['name'], '-') . '.jpg');
            }

            Modules::create([
                'name' => $value['name'],
                'type' => 'system',
                'modules' => json_encode($value)
            ]);
        }

        Cache::flush();
    }

    protected function localModuleMapping($localModules, $json)
    {
        if (
            @$json['category'] == 1 || @$json['category'] == 2 || @$json['category'] == 3 || @$json['category'] == 4 || @$json['category'] == 5 || @$json['category'] == 6 || @$json['category'] == 7 || @$json['category'] == 8 || @$json['category'] == 9
        ) {
            array_push($localModules, $json);
        }

        return $localModules;
    }

    protected function getWebsitePagesJson()
    {
        $websiteModules = [];

        if (is_dir($this->websiteModulesDir)) {
            $files = glob($this->websiteModulesDir . '*', GLOB_ONLYDIR);

            foreach ($files as $file) {
                $json_file = file_get_contents($file . '/website-page.json');
                $json = json_decode($json_file, true);
                $json['preview'] = $file . "/preview.jpg";

                array_push($websiteModules, $json);
            }
        }

        return $websiteModules;
    }

    protected function getLandingPagesJson()
    {
        $landingModules = [];

        if (is_dir($this->landingModulesDir)) {
            $files = glob($this->landingModulesDir . '*', GLOB_ONLYDIR);

            foreach ($files as $file) {
                $json_file = file_get_contents($file . '/landing-page.json');
                $json = json_decode($json_file, true);
                $json['preview'] = $file . "/preview.jpg";

                array_push($landingModules, $json);
            }
        }

        return $landingModules;
    }

    protected function getBlogPagesJson()
    {
        $blogModules = [];

        if (is_dir($this->blogModulesDir)) {
            $files = glob($this->blogModulesDir . '*', GLOB_ONLYDIR);

            foreach ($files as $file) {
                $json_file = file_get_contents($file . '/blog.json');
                $json = json_decode($json_file, true);
                $json['preview'] = $file . "/preview.jpg";

                array_push($blogModules, $json);
            }
        }

        return $blogModules;
    }

    protected function getSystemPagesJson()
    {
        $systemModules = [];

        if (is_dir($this->systemModulesDir)) {
            $files = glob($this->systemModulesDir . '*', GLOB_ONLYDIR);

            foreach ($files as $file) {
                if (file_exists($file . '/template.json')) {
                    $json_file = file_get_contents($file . '/template.json');
                    $json = json_decode($json_file, true);
                    $json['preview'] = $file . "/preview.jpg";

                    array_push($systemModules, $json);
                }
            }
        }

        return $systemModules;
    }

    protected function checkContainFreeModules($modules)
    {
        $modules = json_decode($modules, true);

        $free = 0;
        $paid = 0;

        foreach ($modules['custom_modules'] as $key => $module) {
            $json_file = file_get_contents($this->modulesDir . $module);
            $json = json_decode($json_file, true);

            if (@$json['is_free']) {
                $free += 1;
            } else {
                $paid += 1;
            }
        }

        if ($paid > 0) {
            return false;
        } else {
            return true;
        }
    }
}
