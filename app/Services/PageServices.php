<?php

namespace App\Services;

use App\Models\Pages;
use App\Models\Layouts;
use App\Models\History;
use App\Models\PagesModules;
use App\Models\LayoutsModules;
use App\Models\HistoryModules;
use App\Models\Portals;

class PageServices extends BaseService
{
    protected $modulesDir;

    protected $oauthServices;

    protected $hubspotServices;

    protected $moduleServices;

    protected $userServices;

    public function __construct(OauthServices $oauthServices, HubspotServices $hubspotServices, ModuleServices $moduleServices, UserServices $userServices)
    {
        $this->modulesDir = storage_path('templates/launchpad-starter-v2/sr/sr-templates/');
        $this->oauthServices = $oauthServices;
        $this->hubspotServices = $hubspotServices;
        $this->moduleServices = $moduleServices;
        $this->userServices = $userServices;
    }

    public function getAll($attributes)
    {
        $limit = (@$attributes['limit']) ? $attributes['limit'] : 10;
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $results = $this->queryBuilder(Pages::class, $attributes, $include);

        $results = $results->paginate($limit)->toArray();

        return $this->dataWrapper($results);
    }

    public function getById($id, $attributes = [])
    {
        $include = (@$attributes['include']) ? explode(',', @$attributes['include']) : [];

        $result = Pages::with($include)->where('id', $id);

        return $result->firstOrFail()->toArray();
    }

    public function create(array $data, $user)
    {
        if ($user->roles[0]->name == 'admin') {
            abort(422, 'Sorry, you login as Admin');
        } else {
            return $this->atomic(function () use ($data, $user) {
                $portal = Portals::where('id', $data['portal_id'])->firstOrFail()->toArray();

                $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

                $params = [];
                $params['name'] = $data['name'];

                $widgets = [];
                foreach ($data['modules'] as $module) {
                    if (@$module['custom_module']['global'] && !@$module['custom_module']['override_global']) {
                        $widgetName = $module['custom_module']['name'] . ' (global)';
                    } else {
                        $widgetName = $module['custom_module']['name'];
                    }

                    $widget = [
                        'type' => 'custom_widget',
                        'body' => [
                            'widget_name' => $widgetName
                        ]
                    ];
                    array_push($widgets, $widget);

                    $remoteModules = $this->hubspotServices->getRemoteModulesByName($accessToken['access_token'], $module['custom_module']['name']);

                    if (empty($remoteModules['objects'])) {
                        $moduleData = [
                            'portal_id' => $data['portal_id'],
                            'module' => $module
                        ];

                        $this->moduleServices->install($moduleData, $user);
                    }
                }

                $data['template_path'] = "sr/templates/page.html";
                $data['widget_containers']['main_flexible_column']['widgets'] = $widgets;
                unset($data['portal_id']);

                // Publish Page
                $publishPageData = [
                    "action" => "schedule-publish"
                ];

                // Create Page
                $createPageResponse = $this->hubspotServices->createAndPublishPage($accessToken['access_token'], $data, $publishPageData);

                // Save to DB
                $params['page_id'] = $createPageResponse['id'];
                $params['modules'] = '';
                $params['url'] = $createPageResponse['absolute_url'];
                $params['user_id'] = $user->id;
                $params['agency_id'] = $user->agencies[0]->id;

                $res = Pages::create($params);

                foreach ($data['modules'] as $module) {
                    PagesModules::create([
                        'page_id' => $res->id,
                        'modules' => $module['custom_module']['name']
                    ]);
                }

                if(@$data['history_id']) {
                    $history = History::where('id', $data['history_id'])->first();    
                    $history->status = 1;
                    $history->update();
                }

                return $res;
            });
        }
    }

    public function createlayout(array $data, $user)
    {
        if ($user->roles[0]->name == 'admin') {
            abort(422, 'Sorry, you login as Admin');
        } else {
            return $this->atomic(function () use ($data, $user) {
                $portal = Portals::where('id', $data['portal_id'])->firstOrFail()->toArray();

                $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

                $params = [];
                $params['name'] = $data['name'];

                $widgets = [];
                foreach ($data['modules'] as $module) {
                    if (@$module['custom_module']['global'] && !@$module['custom_module']['override_global']) {
                        $widgetName = $module['custom_module']['name'] . ' (global)';
                    } else {
                        $widgetName = $module['custom_module']['name'];
                    }

                    $widget = [
                        'type' => 'custom_widget',
                        'body' => [
                            'widget_name' => $widgetName
                        ]
                    ];
                    array_push($widgets, $widget);

                    $remoteModules = $this->hubspotServices->getRemoteModulesByName($accessToken['access_token'], $module['custom_module']['name']);

                    if (empty($remoteModules['objects'])) {
                        $moduleData = [
                            'portal_id' => $data['portal_id'],
                            'module' => $module
                        ];

                        $this->moduleServices->install($moduleData, $user);
                    }
                }

                $data['template_path'] = "sr/templates/page.html";
                $data['widget_containers']['main_flexible_column']['widgets'] = $widgets;
                unset($data['portal_id']);

                // Publish Page
                $publishPageData = [
                    "action" => "schedule-publish"
                ];

                // Create Page
                $createPageResponse = $this->hubspotServices->createAndPublishPage($accessToken['access_token'], $data, $publishPageData);

                // Save to DB
                $params['user_id'] = $user->id;
                $params['agency_id'] = $user->agencies[0]->id;

                if(@$data['id']) {
                    $res = Layouts::where('id', $data['id'])->first();
                    $res->name = $data['name'];
                    $res->update();

                    LayoutsModules::where('layout_id', $res->id)->delete();
                    
                    foreach ($data['modules'] as $module) {
                        LayoutsModules::create([
                            'layout_id' => $res->id,
                            'modules' => $module['custom_module']['name']
                        ]);
                    }
                }else {
                    $name = $params['name'];
                    $arrs = Layouts::where('name', 'like', '%' . $name . '%')->get()->toArray();
                    if(count($arrs) > 0) {
                        $params['name'] = $name . " (" . count($arrs) . ")";
                    }
                    $res = Layouts::create($params);

                    foreach ($data['modules'] as $module) {
                        LayoutsModules::create([
                            'layout_id' => $res->id,
                            'modules' => $module['custom_module']['name']
                        ]);
                    }
                }

                return $res;
            });
        }
    }

    public function createhistory(array $data, $user)
    {
        if ($user->roles[0]->name == 'admin') {
            abort(422, 'Sorry, you login as Admin');
        } else {
            return $this->atomic(function () use ($data, $user) {
                $portal = Portals::where('id', $data['portal_id'])->firstOrFail()->toArray();

                $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

                $params = [];
                $params['name'] = $data['name'];

                $widgets = [];
                foreach ($data['modules'] as $module) {
                    if (@$module['custom_module']['global'] && !@$module['custom_module']['override_global']) {
                        $widgetName = $module['custom_module']['name'] . ' (global)';
                    } else {
                        $widgetName = $module['custom_module']['name'];
                    }

                    $widget = [
                        'type' => 'custom_widget',
                        'body' => [
                            'widget_name' => $widgetName
                        ]
                    ];
                    array_push($widgets, $widget);

                    $remoteModules = $this->hubspotServices->getRemoteModulesByName($accessToken['access_token'], $module['custom_module']['name']);

                    if (empty($remoteModules['objects'])) {
                        $moduleData = [
                            'portal_id' => $data['portal_id'],
                            'module' => $module
                        ];

                        $this->moduleServices->install($moduleData, $user);
                    }
                }

                $data['template_path'] = "sr/templates/page.html";
                $data['widget_containers']['main_flexible_column']['widgets'] = $widgets;
                unset($data['portal_id']);

                // Publish Page
                $publishPageData = [
                    "action" => "schedule-publish"
                ];

                // Create Page
                // $createPageResponse = $this->hubspotServices->createAndPublishPage($accessToken['access_token'], $data, $publishPageData);

                // Save to DB
                $params['user_id'] = $user->id;
                $params['agency_id'] = $user->agencies[0]->id;

                if(@$data['historyId']) {
                    $res = History::where('id', $data['historyId'])->first();
                    $res->name = $data['name'];
                    $res->update();

                    HistoryModules::where('history_id', $res->id)->delete();
                    
                    foreach ($data['modules'] as $module) {
                        HistoryModules::create([
                            'history_id' => $res->id,
                            'modules' => $module['custom_module']['name']
                        ]);
                    }
                }else {
                    $name = $params['name'];
                    $arrs = History::where('name', 'like', '%' . $name . '%')->get()->toArray();
                    if(count($arrs) > 0) {
                        $params['name'] = $name . " (" . count($arrs) . ")";
                    }
                    $res = History::create($params);

                    foreach ($data['modules'] as $module) {
                        HistoryModules::create([
                            'history_id' => $res->id,
                            'modules' => $module['custom_module']['name']
                        ]);
                    }
                }

                return $res;
            });
        }
    }

    public function createCustomPage(array $data, $user)
    {
        if ($user->roles[0]->name == 'admin') {
            abort(422, 'Sorry, you login as Admin');
        } else {
            $this->isEligible($data['module'], $user);

            return $this->atomic(function () use ($data, $user) {
                if ($data['type'] == 'website' || $data['type'] == 'landing') {
                    $portal = Portals::where('id', $data['portal_id'])->firstOrFail()->toArray();

                    $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

                    $params = [];
                    $params['name'] = $data['name'];

                    $widgets = [];
                    foreach ($data['module']['custom_modules'] as $customModule) {
                        $json_file = file_get_contents($this->modulesDir . $customModule);
                        $module = json_decode($json_file, true);

                        if (isset($data['module']['module_overrides'])) {
                            if (array_key_exists($customModule, $data['module']['module_overrides'])) {
                                $moduleOverride = $data['module']['module_overrides'][$customModule];
                                $module = array_replace_recursive($module, $moduleOverride);
                            }
                        }

                        $widget = [
                            'type' => 'custom_widget',
                            'body' => [
                                'widget_name' => $module['custom_module']['name']
                            ]
                        ];

                        if (isset($data['module']['page_overrides'])) {
                            if (array_key_exists($customModule, $data['module']['page_overrides'])) {
                                $pageOverride = [
                                    'body' => $data['module']['page_overrides'][$customModule]
                                ];

                                $widget = array_merge_recursive($widget, $pageOverride);
                            }
                        }

                        array_push($widgets, $widget);

                        $remoteModules = $this->hubspotServices->getRemoteModulesByName($accessToken['access_token'], $module['custom_module']['name']);

                        if (empty($remoteModules['objects'])) {
                            $moduleData = [
                                'portal_id' => $data['portal_id'],
                                'module' => $module
                            ];

                            $res = $this->moduleServices->install($moduleData, $user);
                        }
                    }

                    $data['template_path'] = "sr/templates/page.html";
                    $data['widget_containers']['main_flexible_column']['widgets'] = $widgets;
                    unset($data['portal_id']);
                    unset($data['type']);

                    // Create Page
                    $createPageResponse = $this->hubspotServices->createPage($accessToken['access_token'], $data);

                    // Save to DB
                    $params['page_id'] = $createPageResponse['id'];
                    $params['modules'] = '';
                    $params['url'] = $createPageResponse['absolute_url'];
                    $params['user_id'] = $user->id;
                    $params['agency_id'] = $user->agencies[0]->id;

                    $res = Pages::create($params);
                    $res['portal'] = $portal;
                    $res['modules'] = $widgets;

                    foreach ($data['module']['custom_modules'] as $module) {
                        $json_file = file_get_contents($this->modulesDir . $module);
                        $module = json_decode($json_file, true);

                        PagesModules::create([
                            'page_id' => $res->id,
                            'modules' => $module['custom_module']['name']
                        ]);
                    }

                    return $res;
                } else if ($data['type'] == 'system') {
                    $portal = Portals::where('id', $data['portal_id'])->firstOrFail()->toArray();

                    $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

                    foreach ($data['module']['custom_modules'] as $customModule) {
                        $json_file = file_get_contents($this->modulesDir . $customModule);
                        $module = json_decode($json_file, true);

                        if (isset($data['module']['module_overrides'])) {
                            if (array_key_exists($customModule, $data['module']['module_overrides'])) {
                                $moduleOverride = $data['module']['module_overrides'][$customModule];
                                $module = array_replace_recursive($module, $moduleOverride);
                            }
                        }

                        $remoteModules = $this->hubspotServices->getRemoteModulesByName($accessToken['access_token'], $module['custom_module']['name']);

                        if (empty($remoteModules['objects'])) {
                            $moduleData = [
                                'portal_id' => $data['portal_id'],
                                'module' => $module
                            ];

                            $res = $this->moduleServices->install($moduleData, $user);
                        }
                    }

                    $template = $this->uploadTemplate($data['module']['templates'], $accessToken);

                    return $template;
                } else {
                    $portal = Portals::where('id', $data['portal_id'])->firstOrFail()->toArray();

                    $accessToken = $this->oauthServices->refreshAccessToken($portal, $user);

                    $template = $this->uploadTemplate($data['module']['template'], $accessToken);
                    $files = [];

                    return [
                        'portal' => $portal,
                        'status' => 'success',
                        'templates' => $template,
                        'files' => $files
                    ];
                }
            });
        }
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

    protected function uploadTemplate($templates, $accessToken)
    {
        $results = [];

        foreach ($templates as $key => $template) {
            $template['source'] = file_get_contents(storage_path('templates') . '/' . $template['source']);

            $response = $this->hubspotServices->uploadTemplate($accessToken['access_token'], $template);

            if ($response->getStatusCode() == 201) {
                $uploadRes = [
                    'path' => $template['path'],
                    'status' => 'success',
                ];
                array_push($results, $uploadRes);
            } else {
                $uploadRes = [
                    'path' => $template['path'],
                    'status' => 'file already exists',
                ];
                array_push($results, $uploadRes);
            }
        }

        return $results;
    }

    public function createCustomPageTemplate(array $data, $user)
    {
    }

    public function update($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $result = Pages::where('id', $id)->update($data);

            return $result;
        });
    }

    public function delete($id)
    {
        Pages::where('id', $id)->delete();
    }
}
