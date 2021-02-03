<?php

namespace App\Services;

use App\Models\Modules;
use App\Models\Portals;
use Cache;
use DB;
use Str;

class ModuleServices extends BaseService
{
    protected $modulesDir;

    protected $websiteModulesDir;

    protected $landingModulesDir;

    protected $blogModulesDir;

    protected $filesDir;

    protected $hubspotServices;

    protected $oauthServices;

    protected $userServices;

    public function __construct(OauthServices $oauthServices, HubspotServices $hubspotServices, UserServices $userServices)
    {
        $this->modulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/');
        $this->websiteModulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/website-pages/');
        $this->landingModulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/landing-pages/');
        $this->blogModulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/blog/');
        $this->filesDir = storage_path('templates/');
        $this->hubspotServices = $hubspotServices;
        $this->oauthServices = $oauthServices;
        $this->userServices = $userServices;
    }

    public function getAllByCategories($attributes, $isSearch = false)
    {
        $localModules = $this->createLocalModuleByCategory();

        // Get Modules
        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Modules::class, $attributes, $include);

        $results = $results->where('type', 'modules');

        if ($isSearch) {
            $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
        }

        $results = $results->paginate($limit)->toArray();

        $results = $this->dataWrapper($results);

        if ($isSearch) {
            foreach ($results['data'] as $key => $value) {
                $results['data'][$key]['modules']['custom_module']['module_preview'] = '/module-previews/' . Str::slug($value['name'], '-') . '.jpg';
            }

            return $results;
        } else {
            foreach ($results['data'] as $result) {
                $localModules = $this->localModuleMappingByCategory($localModules, $result['modules']);
            }

            return $localModules;
        }
    }

    protected function createLocalModuleByCategory()
    {
        $localModule = [
            'navigation' => [
                'name' => 'Navigation',
                'key' => 'navigation',
                'modules' => [],
            ],
            'hero' => [
                'name' => 'Hero',
                'key' => 'hero',
                'modules' => [],
            ],
            'footer' => [
                'name' => 'Footer',
                'key' => 'footer',
                'modules' => [],
            ],
            'columns' => [
                'name' => 'Columns',
                'key' => 'columns',
                'modules' => [],
            ],
            'tabs' => [
                'name' => 'Tabs',
                'key' => 'tabs',
                'modules' => [],
            ],
            'offers' => [
                'name' => 'Offers',
                'key' => 'offers',
                'modules' => [],
            ],
            'cards' => [
                'name' => 'Cards',
                'key' => 'cards',
                'modules' => [],
            ],
            'social' => [
                'name' => 'Social',
                'key' => 'social',
                'modules' => [],
            ],
            'bling' => [
                'name' => 'Bling',
                'key' => 'bling',
                'modules' => [],
            ],
        ];

        return $localModule;
    }

    protected function localModuleMappingByCategory($localModules, $json)
    {
        if (@$json['custom_module']['name']) {
            $json['custom_module']['module_preview'] = '/module-previews/' . Str::slug($json['custom_module']['name'], '-') . '.jpg';
        }

        if (@$json['category'] == 1) {
            array_push($localModules['navigation']['modules'], $json);
        } else if (@$json['category'] == 2) {
            array_push($localModules['footer']['modules'], $json);
        } else if (@$json['category'] == 3) {
            array_push($localModules['hero']['modules'], $json);
        } else if (@$json['category'] == 4) {
            array_push($localModules['columns']['modules'], $json);
        } else if (@$json['category'] == 5) {
            array_push($localModules['tabs']['modules'], $json);
        } else if (@$json['category'] == 6) {
            array_push($localModules['offers']['modules'], $json);
        } else if (@$json['category'] == 7) {
            array_push($localModules['cards']['modules'], $json);
        } else if (@$json['category'] == 8) {
            array_push($localModules['social']['modules'], $json);
        } else if (@$json['category'] == 9) {
            array_push($localModules['bling']['modules'], $json);
        }

        return $localModules;
    }

    public function getWebsitePages($attributes, $isSearch = false)
    {
        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Modules::class, $attributes, $include);

        $results = $results->where('type', 'website-page');

        if ($isSearch) {
            $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
        }

        $results = $results->paginate($limit)->toArray();

        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['module_preview'] = '/module-previews/' . Str::slug($value['name'], '-') . '.jpg';
        }

        return $this->dataWrapper($results);
    }

    public function getLandingPages($attributes, $isSearch = false)
    {
        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Modules::class, $attributes, $include);

        $results = $results->where('type', 'landing-page');

        if ($isSearch) {
            $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
        }

        $results = $results->paginate($limit)->toArray();

        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['module_preview'] = '/module-previews/' . Str::slug($value['name'], '-') . '.jpg';
        }

        return $this->dataWrapper($results);
    }

    public function getBlogPages($attributes, $isSearch = false)
    {
        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Modules::class, $attributes, $include);

        $results = $results->where('type', 'blog');

        if ($isSearch) {
            $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
        }

        $results = $results->paginate($limit)->toArray();

        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['module_preview'] = '/module-previews/' . Str::slug($value['name'], '-') . '.jpg';
        }

        return $this->dataWrapper($results);
    }

    public function getSystemPages($attributes, $isSearch = false)
    {
        $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Modules::class, $attributes, $include);

        $results = $results->where('type', 'system');

        if ($isSearch) {
            $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
        }

        $results = $results->paginate($limit)->toArray();

        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['module_preview'] = '/module-previews/' . Str::slug($value['name'], '-') . '.jpg';
        }

        return $this->dataWrapper($results);
    }

    public function install($attributes, $user)
    {
        $portal = Portals::where('id', $attributes['portal_id'])->firstOrFail()->toArray();

        $this->isEligible($attributes['module'], $user);

        $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

        if (@$attributes['mode'] == 'group') {
            $res['install_results'] = $this->uploadGroupModule($attributes['module'], $accessToken);
            $res['data'] = $this->getAll($attributes, $attributes['portal_id'], false, $user);

            Cache::forget('getAllModules:' . $attributes['portal_id']);

            return $res;
        } else {

            if (@$attributes['module']['custom_module']['global']) {
                $attributesGlobal = $attributes;

                $this->uploadModule($attributesGlobal['module'], $accessToken);

                $attributes['module']['custom_module']['global'] = false;
            }

            Cache::forget('getAllModules:' . $attributes['portal_id']);

            return $this->uploadModule($attributes['module'], $accessToken);
        }
    }

    public function upgrade($attributes, $user)
    {
        $portal = Portals::where('id', $attributes['portal_id'])->firstOrFail()->toArray();

        $this->isEligible($attributes['module'], $user);

        $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

        if (@$attributes['module']['custom_module']['global']) {
            $attributesGlobal = $attributes;

            $this->upgradeModule($attributesGlobal, $accessToken, $attributes['globalModuleId']);

            $attributes['module']['custom_module']['global'] = false;
        }

        Cache::forget('getAllModules:' . $attributes['portal_id']);

        return $this->upgradeModule($attributes, $accessToken, $attributes['moduleId']);
    }

    protected function isEligible($modules, $user)
    {
        $user = $this->userServices->getByLoggedUser($user->id);

        $isFreeModule = @$modules['is_free'] ? true : false;

        if (($user['agencies']['plans']['type'] == 'free' && !$isFreeModule) &&
            ($user['agencies']['plans']['type'] == 'free' && $user['agencies']['plans']['is_hidden_plan'] == 'no' && !$isFreeModule)
        ) {
            abort(422, 'You are not eligible to do this action');
        }
    }

    protected function uploadGroupModule($attributes, $accessToken)
    {
        $results = [];

        foreach ($attributes['custom_module_group']['custom_modules'] as $module) {
            if (file_exists($this->modulesDir . $module)) {
                $json_file = file_get_contents($this->modulesDir . $module);
                $json = json_decode($json_file, true);

                if (@$json['custom_module']['global']) {
                    $attributesGlobal = $json;

                    $res = $this->uploadModule($attributesGlobal, $accessToken);

                    array_push($results, $res);

                    $json['custom_module']['global'] = false;
                }

                $res = $this->uploadModule($json, $accessToken);

                array_push($results, $res);
            } else {
                array_push($results, [
                    'name' => $module,
                    'status' => false
                ]);
            }
        }

        return $results;
    }

    public function uploadModule($attributes, $accessToken)
    {
        $folderId = $this->hubspotServices->getFolderId($accessToken['access_token']);

        if (@$attributes['custom_module']['global'] && !@$attributes['custom_module']['override_global']) {
            $attributes['custom_module']['name'] = $attributes['custom_module']['name'] . ' (global)';
            $attributes['custom_module']['legacyName'] = $attributes['custom_module']['name'];
        } else {
            $attributes['custom_module']['legacyName'] = $attributes['custom_module']['name'];
        }

        if ($attributes['custom_module_template']) {
            $attributes['custom_module']['source'] = file_get_contents($this->modulesDir . $attributes['custom_module_template']);
            $attributes['custom_module']['folderId'] = $folderId;
        }

        if ($attributes['files']) {
            foreach ($attributes['files'] as $file) {
                $this->hubspotServices->uploadFiles($accessToken['access_token'], $file);
            }
        }

        return $this->hubspotServices->uploadModules($accessToken['access_token'], $attributes['custom_module']);
    }

    public function upgradeModule($attributes, $accessToken, $moduleId)
    {
        $folderId = $this->hubspotServices->getFolderId($accessToken['access_token']);

        if (@$attributes['module']['custom_module']['global'] && !@$attributes['module']['custom_module']['override_global']) {
            $attributes['module']['custom_module']['name'] = $attributes['module']['custom_module']['name'] . ' (global)';
            $attributes['module']['custom_module']['legacyName'] = $attributes['module']['custom_module']['name'];
        } else {
            $attributes['module']['custom_module']['legacyName'] = $attributes['module']['custom_module']['name'];
        }

        if ($attributes['module']['custom_module_template']) {
            $attributes['module']['custom_module']['source'] = file_get_contents($this->modulesDir . $attributes['module']['custom_module_template']);
            $attributes['module']['custom_module']['folderId'] = $folderId;
        }

        if ($attributes['module']['files']) {
            foreach ($attributes['module']['files'] as $file) {
                $this->hubspotServices->uploadFiles($accessToken['access_token'], $file);
            }
        }

        $this->hubspotServices->upgradeModules($accessToken['access_token'], $attributes['module']['custom_module'], $moduleId);

        return $this->hubspotServices->publishModules($accessToken['access_token'], $attributes['module']['custom_module'], $moduleId);
    }

    public function getAll($attributes, $portalId, $isSearch = false, $user)
    {
        if ($portalId) {
            $localModules = [];
            $portal = Portals::where('id', $portalId)->firstOrFail()->toArray();
            $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

            // Get Modules
            $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
            $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

            $results = $this->queryBuilder(Modules::class, $attributes, $include);

            $results = $results->where('type', 'modules');

            if ($isSearch) {
                $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
            }

            $results = $results->paginate($limit)->toArray();

            $localModules = $this->dataWrapper($results);

            // Get Group Modules
            $limit = (@$attributes['limit']) ? $attributes['limit'] : 1000;
            $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

            $results = $this->queryBuilder(Modules::class, $attributes, $include);

            $results = $results->where('type', 'group-modules');

            if ($isSearch) {
                $results = $results->where('name', 'like', '%' . $attributes['keyword'] . '%');
            }

            $results = $results->paginate($limit)->toArray();

            $groupModules = $this->dataWrapper($results);

            $remoteModules = $this->hubspotServices->getRemoteModules($accessToken['access_token']);

            foreach ($localModules['data'] as $key => $localModule) {
                foreach ($remoteModules['objects'] as $remoteModule) {
                    if ($localModule['modules']['custom_module']['name'] == $remoteModule['name'] && $remoteModule['path'] == '/sr/custom-modules/' . $remoteModule['name']) 
                    {
                        $localModules['data'][$key]['installed'] = true;
                        $localModules['data'][$key]['moduleId'] = $remoteModule['moduleId'];
                        $localModules['data'][$key]['globalModuleId'] = $this->getGlobalModuleId($localModule['modules']['custom_module']['name'], $remoteModules['objects']);
                        $localModules['data'][$key]['custom_module']['global'] = $remoteModule['global'];
                        $localModules['data'][$key]['installed_version'] = '---';

                        if (!empty($remoteModule['tags'])) 
                        {
                            $remoteVersion = $this->versionGetAndCheck($remoteModule['tags']);
                            $localVersion = $this->versionGetAndCheck($localModules['data'][$key]['modules']['custom_module']['tags']);

                            if ($this->versionCompare($localVersion, $remoteVersion) == 2)
                            {
                                $localModules['data'][$key]['upgradeable'] = true;
                                $localModules['data'][$key]['upgradeable_type'] = 'major';
                                $localModules['data'][$key]['button_state'] = 'Upgrade Major Version';
                                $localModules['data'][$key]['reinstall'] = false;
                            }
                            else if ($this->versionCompare($localVersion, $remoteVersion) == 1)
                            {
                                $localModules['data'][$key]['upgradeable'] = true;
                                $localModules['data'][$key]['upgradeable_type'] = 'minor';
                                $localModules['data'][$key]['button_state'] = 'Upgrade Module';
                                $localModules['data'][$key]['reinstall'] = false;
                            }
                            else
                            {
                                $localModules['data'][$key]['upgradeable'] = false;
                                $localModules['data'][$key]['upgradeable_type'] = 'none';
                                $localModules['data'][$key]['button_state'] = 'Installed';
                                $localModules['data'][$key]['reinstall'] = false;
                            }
                            
                            $localModules['data'][$key]['installed_version'] = $remoteVersion;
                        }
                        else 
                        {
                            $localModules['data'][$key]['reinstall'] = true;
                            $localModules['data'][$key]['button_state'] = 'Upgrade Module';
                        }

                        break;
                    } 
                    else 
                    {
                        $localModules['data'][$key]['installed'] = false;
                        $localModules['data'][$key]['installed_version'] = '---';
                        $localModules['data'][$key]['reinstall'] = false;
                        $localModules['data'][$key]['button_state'] = 'Install Module';
                    }
                }
            }

            foreach ($groupModules['data'] as $key => $groupModule) {
                $matchChecker = [];
                foreach ($groupModule['modules']['custom_module_group']['custom_modules'] as $customModules) {
                    foreach ($remoteModules['objects'] as $remoteModule) {
                        if (file_exists($this->modulesDir . $customModules)) {
                            if (json_decode(file_get_contents($this->modulesDir . $customModules), true)['custom_module']['name'] == str_replace(" (global)", "", $remoteModule['name'])) {
                                array_push($matchChecker, $customModules);
                                break;
                            }
                        }
                    }
                }

                if (count($groupModule['modules']['custom_module_group']['custom_modules']) == count($matchChecker)) {
                    $groupModules['data'][$key]['installed'] = true;
                    $groupModules['data'][$key]['custom_module']['global'] = $remoteModule['global'];
                    $groupModules['data'][$key]['installed_version'] = '---';
                } else {
                    $groupModules['data'][$key]['installed'] = false;
                    $groupModules['data'][$key]['installed_version'] = '---';
                }
            }

            $results = [
                'group' => $groupModules['data'],
                'all' => $localModules['data']
            ];

            return $results;
        } else {
            abort(403, 'Please select portal');
        }
    }

    public function upgradeInformation($portalId, $user)
    {
        $portal = Portals::where('id', $portalId)->firstOrFail()->toArray();

        $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

        $modules = $this->queryBuilder(Modules::class, [], []);

        $modules = $modules->where('type', 'modules');

        $modules = $modules->paginate(1000)->toArray();

        $localModules = $this->dataWrapper($modules);
        
        $remoteModules = $this->hubspotServices->getRemoteModules($accessToken['access_token']);

        $information = [
            'is_upgrade_available' => false
        ];

        foreach ($localModules['data'] as $key => $localModule) {
            foreach ($remoteModules['objects'] as $remoteModule) {
                if ($localModule['modules']['custom_module']['name'] == $remoteModule['name'] && $remoteModule['path'] == '/sr/custom-modules/' . $remoteModule['name']) 
                {
                    if (!empty($remoteModule['tags'])) 
                    {
                        $remoteVersion = $this->versionGetAndCheck($remoteModule['tags']);
                        $localVersion = $this->versionGetAndCheck($localModules['data'][$key]['modules']['custom_module']['tags']);

                        if ($this->versionCompare($localVersion, $remoteVersion) == 2)
                        {
                            $information['is_upgrade_available'] = true;

                            return ['information' => $information];
                        }
                        else if ($this->versionCompare($localVersion, $remoteVersion) == 1)
                        {
                            $information['is_upgrade_available'] = true;

                            return ['information' => $information];
                        }
                    }

                    break;
                }
            }
        }

        return ['information' => $information];
    }

    public function audit($user)
    {
        $sqlQuery = "SELECT m.id, m.name, count(pm.modules) as total_install
		FROM modules m
		LEFT OUTER JOIN pages_modules pm ON m.name = pm.modules
		WHERE m.type = 'modules'
		GROUP BY m.id";

        $result = DB::select(DB::raw($sqlQuery));

        return $result;
    }

    protected function getGlobalModuleId($localModules, $remoteModules)
    {
        foreach ($remoteModules as $remoteModule) {
            if ($localModules . ' (global)' == $remoteModule['name']) {
                return $remoteModule['moduleId'];
            }
        }
    }

    protected function versionGetAndCheck($tags)
    {
        foreach($tags as $tag) {
            if (substr($tag, 0, 3) == "ver") {
                $ver = explode('-', $tag);
                $ver = $ver[1];
                
                return $ver;
            } else {
                return false;
            }
        }
    }

    protected function versionCompare($localVersion, $remoteVersion)
    {
        $localVersion = explode('.', $localVersion);
        $localMajor = $localVersion[0];
        $localMinor = $localVersion[1];
        $localFix = $localVersion[2];

        $remoteVersion = explode('.', $remoteVersion);
        $remoteMajor = $remoteVersion[0];
        $remoteMinor = $remoteVersion[1];
        $remoteFix = $remoteVersion[2];

        if ($localMajor > $remoteMajor)
        {
            return 2;
        }

        if ($localMinor > $remoteMinor || $localFix > $remoteFix)
        {
            return 1;
        }

        return 0;
    }
}
