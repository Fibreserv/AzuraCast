<?php
use App\Controller;
use App\Middleware;

return function(\Slim\App $app)
{
    $app->group('/admin', function () {

        $this->get('', Controller\Admin\IndexController::class.':indexAction')
            ->setName('admin:index:index');

        $this->get('/sync/{type}', Controller\Admin\IndexController::class.':syncAction')
            ->setName('admin:index:sync')
            ->add([Middleware\Permissions::class, 'administer all']);

        $this->group('/install', function () {

            $this->map(['GET', 'POST'], '/shoutcast', Controller\Admin\Install\ShoutcastController::class)
                ->setName('admin:install:shoutcast');

        })->add([Middleware\Permissions::class, 'administer all']);

        $this->group('/api', function () {

            $this->get('', Controller\Admin\ApiController::class.':indexAction')
                ->setName('admin:api:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Admin\ApiController::class.':editAction')
                ->setName('admin:api:edit');

            $this->get('/delete/{id}/{csrf}', Controller\Admin\ApiController::class.':deleteAction')
                ->setName('admin:api:delete');

        })->add([Middleware\Permissions::class, 'administer api keys']);

        $this->map(['GET', 'POST'], '/branding', Controller\Admin\BrandingController::class.':indexAction')
            ->setName('admin:branding:index')
            ->add([Middleware\Permissions::class, 'administer settings']);

        $this->group('/custom_fields', function() {

            $this->get('', Controller\Admin\CustomFieldsController::class.':indexAction')
                ->setName('admin:custom_fields:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Admin\CustomFieldsController::class.':editAction')
                ->setName('admin:custom_fields:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Admin\CustomFieldsController::class.':editAction')
                ->setName('admin:custom_fields:add');

            $this->get('/delete/{id}/{csrf}', Controller\Admin\CustomFieldsController::class.':deleteAction')
                ->setName('admin:custom_fields:delete');

        })->add([Middleware\Permissions::class, 'administer custom fields']);

        $this->group('/permissions', function () {

            $this->get('', Controller\Admin\PermissionsController::class.':indexAction')
                ->setName('admin:permissions:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Admin\PermissionsController::class.':editAction')
                ->setName('admin:permissions:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Admin\PermissionsController::class.':editAction')
                ->setName('admin:permissions:add');

            $this->get('/delete/{id}/{csrf}', Controller\Admin\PermissionsController::class.':deleteAction')
                ->setName('admin:permissions:delete');

        })->add([Middleware\Permissions::class, 'administer permissions']);

        $this->map(['GET', 'POST'], '/settings', Controller\Admin\SettingsController::class.':indexAction')
            ->setName('admin:settings:index')
            ->add([Middleware\Permissions::class, 'administer settings']);

        $this->group('/stations', function () {

            $this->get('', Controller\Admin\StationsController::class.':indexAction')
                ->setName('admin:stations:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Admin\StationsController::class.':editAction')
                ->setName('admin:stations:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Admin\StationsController::class.':editAction')
                ->setName('admin:stations:add');

            $this->map(['GET', 'POST'], '/clone/{id}', Controller\Admin\StationsController::class.':cloneAction')
                ->setName('admin:stations:clone');

            $this->get('/delete/{id}/{csrf}', Controller\Admin\StationsController::class.':deleteAction')
                ->setName('admin:stations:delete');

        })->add([Middleware\Permissions::class, 'administer stations']);

        $this->group('/users', function () {

            $this->get('', Controller\Admin\UsersController::class.':indexAction')
                ->setName('admin:users:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Admin\UsersController::class.':editAction')
                ->setName('admin:users:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Admin\UsersController::class.':editAction')
                ->setName('admin:users:add');

            $this->get('/delete/{id}/{csrf}', Controller\Admin\UsersController::class.':deleteAction')
                ->setName('admin:users:delete');

            $this->get('/login-as/{id}/{csrf}', Controller\Admin\UsersController::class.':impersonateAction')
                ->setName('admin:users:impersonate');

        })->add([Middleware\Permissions::class, 'administer users']);

        // END /admin GROUP

    })
        ->add(Middleware\Module\Admin::class)
        ->add(Middleware\EnableView::class)
        ->add([Middleware\Permissions::class, 'view administration'])
        ->add(Middleware\RequireLogin::class);

    $app->group('/api', function () {

        $this->options('/{routes:.+}', function (\App\Http\Request $request, \App\Http\Response $response) {
            return $response
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'x-requested-with, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Origin', '*');
        });

        $this->get('', Controller\Api\IndexController::class.':indexAction')
            ->setName('api:index:index');

        $this->get('/status', Controller\Api\IndexController::class.':statusAction')
            ->setName('api:index:status');

        $this->get('/time', Controller\Api\IndexController::class.':timeAction')
            ->setName('api:index:time');

        $this->group('/internal', function () {

            $this->group('/{station}', function() {

                // Liquidsoap internal authentication functions
                $this->map(['GET', 'POST'], '/auth', Controller\Api\InternalController::class.':authAction')
                    ->setName('api:internal:auth');

                $this->map(['GET', 'POST'], '/nextsong', Controller\Api\InternalController::class.':nextsongAction')
                    ->setName('api:internal:nextsong');

                $this->map(['GET', 'POST'], '/djon', Controller\Api\InternalController::class.':djonAction')
                    ->setName('api:internal:djon');

                $this->map(['GET', 'POST'], '/djoff', Controller\Api\InternalController::class.':djoffAction')
                    ->setName('api:internal:djoff');

                // Station-watcher connection endpoint
                $this->map(['GET', 'POST'], '/notify', Controller\Api\InternalController::class.':notifyAction')
                    ->setName('api:internal:notify');

            })->add(Middleware\GetStation::class);

        });

        $this->get('/nowplaying[/{station}]', Controller\Api\NowplayingController::class)
            ->setName('api:nowplaying:index');

        $this->get('/stations', Controller\Api\Stations\IndexController::class.':listAction')
            ->setName('api:stations:list')
            ->add([Middleware\RateLimit::class, 'api', 5, 2]);

        $this->group('/station/{station}', function () {

            $this->get('', Controller\Api\Stations\IndexController::class.':indexAction')
                ->setName('api:stations:index')
                ->add([Middleware\RateLimit::class, 'api', 5, 2]);

            $this->get('/nowplaying', Controller\Api\NowplayingController::class.':indexAction');

            // This would not normally be POST-able, but Bootgrid requires it
            $this->map(['GET', 'POST'], '/history', Controller\Api\Stations\HistoryController::class)
                ->setName('api:stations:history')
                ->add([Middleware\Permissions::class, 'view station reports', true]);

            // This would not normally be POST-able, but Bootgrid requires it
            $this->map(['GET', 'POST'], '/requests', Controller\Api\RequestsController::class.':listAction')
                ->setName('api:requests:list');

            $this->map(['GET', 'POST'], '/request/{media_id}', Controller\Api\RequestsController::class.':submitAction')
                ->setName('api:requests:submit')
                ->add([Middleware\RateLimit::class, 'api', 5, 2]);

            $this->get('/listeners', Controller\Api\ListenersController::class.':indexAction')
                ->setName('api:listeners:index')
                ->add([Middleware\Permissions::class, 'view station reports', true]);

            $this->get('/art/{media_id:[a-zA-Z0-9]+}.jpg', Controller\Api\Stations\MediaController::class.':artAction')
                ->setName('api:stations:media:art');

            $this->get('/art/{media_id:[a-zA-Z0-9]+}', Controller\Api\Stations\MediaController::class.':artAction');

            $this->post('/backend/{do}', Controller\Api\Stations\ServicesController::class.':backendAction')
                ->setName('api:stations:backend')
                ->add([Middleware\Permissions::class, 'manage station broadcasting', true]);

            $this->post('/frontend/{do}', Controller\Api\Stations\ServicesController::class.':frontendAction')
                ->setName('api:stations:frontend')
                ->add([Middleware\Permissions::class, 'manage station broadcasting', true]);

            $this->post('/restart', Controller\Api\Stations\ServicesController::class.':restartAction')
                ->setName('api:stations:restart')
                ->add([Middleware\Permissions::class, 'manage station broadcasting', true]);

        })->add(Middleware\GetStation::class);

        // END /api GROUP

    })
        ->add(Middleware\Module\Api::class);

    $app->get('/', Controller\Frontend\IndexController::class.':indexAction')
        ->setName('home');

    $app->group('', function() {

        $this->get('/dashboard', Controller\Frontend\DashboardController::class.':indexAction')
            ->setName('dashboard');

        $this->get('/logout', Controller\Frontend\AccountController::class.':logoutAction')
            ->setName('account:logout');

        $this->get('/endsession', Controller\Frontend\AccountController::class.':endmasqueradeAction')
            ->setName('account:endmasquerade');

        $this->get('/profile', Controller\Frontend\ProfileController::class.':indexAction')
            ->setName('profile:index');

        $this->map(['GET', 'POST'], '/profile/edit', Controller\Frontend\ProfileController::class.':editAction')
            ->setName('profile:edit');

        $this->get('/api_keys', Controller\Frontend\ApiKeysController::class.':indexAction')
            ->setName('api_keys:index');

        $this->map(['GET', 'POST'], '/api_keys/edit/{id}', Controller\Frontend\ApiKeysController::class.':editAction')
            ->setName('api_keys:edit');

        $this->map(['GET', 'POST'], '/api_keys/add', Controller\Frontend\ApiKeysController::class.':editAction')
            ->setName('api_keys:add');

        $this->get('/api_keys/delete/{id}/{csrf}', Controller\Frontend\ApiKeysController::class.':deleteAction')
            ->setName('api_keys:delete');

        // Used for internal development
        if (!APP_IN_PRODUCTION) {
            $this->any('/test', Controller\Frontend\UtilController::class.':testAction')
                ->setName('util:test');
        }

    })
        ->add(Middleware\EnableView::class)
        ->add(Middleware\RequireLogin::class);

    $app->map(['GET', 'POST'], '/login', Controller\Frontend\AccountController::class.':loginAction')
        ->setName('account:login')
        ->add(Middleware\EnableView::class);

    $app->group('/setup', function () {

        $this->map(['GET', 'POST'], '', Controller\Frontend\SetupController::class.':indexAction')
            ->setName('setup:index');

        $this->map(['GET', 'POST'], '/complete', Controller\Frontend\SetupController::class.':completeAction')
            ->setName('setup:complete');

        $this->map(['GET', 'POST'], '/register', Controller\Frontend\SetupController::class.':registerAction')
            ->setName('setup:register');

        $this->map(['GET', 'POST'], '/station', Controller\Frontend\SetupController::class.':stationAction')
            ->setName('setup:station');

        $this->map(['GET', 'POST'], '/settings', Controller\Frontend\SetupController::class.':settingsAction')
            ->setName('setup:settings');

    })
        ->add(Middleware\EnableView::class);

    $app->group('/public/{station}', function () {

        $this->get('[/{autoplay:autoplay}]', Controller\Frontend\PublicController::class.':indexAction')
            ->setName('public:index');

        $this->get('/embed[/{autoplay:autoplay}]', Controller\Frontend\PublicController::class.':embedAction')
            ->setName('public:embed');

        $this->get('/embed-requests', Controller\Frontend\PublicController::class.':embedrequestsAction')
            ->setName('public:embedrequests');

        $this->get('/playlist[/{format}]', Controller\Frontend\PublicController::class.':playlistAction')
            ->setName('public:playlist');

    })
        ->add(Middleware\GetStation::class)
        ->add(Middleware\EnableView::class);

    $app->group('/station/{station}', function () {

        $this->get('', function (\App\Http\Request $request, \App\Http\Response $response) {
            return $response->withRedirect($request->getRouter()->fromHere('stations:profile:index'));
        })->setName('stations:index:index');

        $this->group('/automation', function () {

            $this->map(['GET', 'POST'], '', Controller\Stations\AutomationController::class.':indexAction')
                ->setName('stations:automation:index');

            $this->get('/run', Controller\Stations\AutomationController::class.':runAction')
                ->setName('stations:automation:run');

        })->add([Middleware\Permissions::class, 'manage station automation', true]);

        $this->group('/files', function () {

            $this->get('', Controller\Stations\Files\FilesController::class.':indexAction')
                ->setName('stations:files:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Stations\Files\EditController::class.':editAction')
                ->setName('stations:files:edit');

            $this->map(['GET', 'POST'], '/rename/{path}', Controller\Stations\Files\FilesController::class.':renameAction')
                ->setName('stations:files:rename');

            $this->map(['GET', 'POST'], '/list', Controller\Stations\Files\FilesController::class.':listAction')
                ->setName('stations:files:list');

            $this->map(['GET', 'POST'], '/batch', Controller\Stations\Files\FilesController::class.':batchAction')
                ->setName('stations:files:batch');

            $this->map(['GET', 'POST'], '/mkdir', Controller\Stations\Files\FilesController::class.':mkdirAction')
                ->setName('stations:files:mkdir');

            $this->map(['GET', 'POST'], '/upload', Controller\Stations\Files\FilesController::class.':uploadAction')
                ->setName('stations:files:upload');

            $this->map(['GET', 'POST'], '/download', Controller\Stations\Files\FilesController::class.':downloadAction')
                ->setName('stations:files:download');

        })
            ->add(Middleware\Module\StationFiles::class)
            ->add([Middleware\Permissions::class, 'manage station media', true]);

        $this->group('/playlists', function () {

            $this->get('', Controller\Stations\PlaylistsController::class.':indexAction')
                ->setName('stations:playlists:index');

            $this->get('/schedule', Controller\Stations\PlaylistsController::class.':scheduleAction')
                ->setName('stations:playlists:schedule');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Stations\PlaylistsController::class.':editAction')
                ->setName('stations:playlists:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Stations\PlaylistsController::class.':editAction')
                ->setName('stations:playlists:add');

            $this->get('/delete/{id}/{csrf}', Controller\Stations\PlaylistsController::class.':deleteAction')
                ->setName('stations:playlists:delete');

            $this->map(['GET', 'POST'], '/reorder/{id}', Controller\Stations\PlaylistsController::class.':reorderAction')
                ->setName('stations:playlists:reorder');

            $this->get('/export/{id}[/{format}]', Controller\Stations\PlaylistsController::class.':exportAction')
                ->setName('stations:playlists:export');

        })->add([Middleware\Permissions::class, 'manage station media', true]);

        $this->group('/mounts', function () {

            $this->get('', Controller\Stations\MountsController::class.':indexAction')
                ->setName('stations:mounts:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Stations\MountsController::class.':editAction')
                ->setName('stations:mounts:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Stations\MountsController::class.':editAction')
                ->setName('stations:mounts:add');

            $this->get('/delete/{id}/{csrf}', Controller\Stations\MountsController::class.':deleteAction')
                ->setName('stations:mounts:delete');

        })->add([Middleware\Permissions::class, 'manage station mounts', true]);

        $this->get('/profile', Controller\Stations\Profile\IndexController::class)
            ->setName('stations:profile:index');

        $this->map(['GET', 'POST'], '/profile/edit', Controller\Stations\Profile\EditController::class)
            ->setName('stations:profile:edit')
            ->add([Middleware\Permissions::class, 'manage station profile', true]);

        $this->group('/remotes', function () {

            $this->get('', Controller\Stations\RemotesController::class.':indexAction')
                ->setName('stations:remotes:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Stations\RemotesController::class.':editAction')
                ->setName('stations:remotes:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Stations\RemotesController::class.':editAction')
                ->setName('stations:remotes:add');

            $this->get('/delete/{id}/{csrf}', Controller\Stations\RemotesController::class.':deleteAction')
                ->setName('stations:remotes:delete');

        })->add([Middleware\Permissions::class, 'manage station remotes', true]);

        $this->group('/reports', function () {

            $this->get('/overview', Controller\Stations\Reports\OverviewController::class)
                ->setName('stations:reports:overview');

            $this->get('/timeline[/format/{format}]', Controller\Stations\Reports\TimelineController::class)
                ->setName('stations:reports:timeline');

            $this->get('/performance[/format/{format}]', Controller\Stations\Reports\PerformanceController::class)
                ->setName('stations:reports:performance');

            $this->get('/duplicates', Controller\Stations\Reports\DuplicatesController::class)
                ->setName('stations:reports:duplicates');

            $this->get('/duplicates/delete/{media_id}', Controller\Stations\Reports\DuplicatesController::class.':deleteAction')
                ->setName('stations:reports:duplicates:delete');

            $this->map(['GET', 'POST'], '/listeners', Controller\Stations\Reports\ListenersController::class)
                ->setName('stations:reports:listeners');

            $this->map(['GET', 'POST'], '/soundexchange', Controller\Stations\Reports\SoundExchangeController::class)
                ->setName('stations:reports:soundexchange');

            $this->get('/requests', Controller\Stations\Reports\RequestsController::class)
                ->setName('stations:reports:requests');

            $this->get('/requests/delete/{request_id}/{csrf}', Controller\Stations\Reports\RequestsController::class.':deleteAction')
                ->setName('stations:reports:requests:delete');

        })->add([Middleware\Permissions::class, 'view station reports', true]);

        $this->group('/streamers', function () {

            $this->get('', Controller\Stations\StreamersController::class.':indexAction')
                ->setName('stations:streamers:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Stations\StreamersController::class.':editAction')
                ->setName('stations:streamers:edit');

            $this->map(['GET', 'POST'], '/add', Controller\Stations\StreamersController::class.':editAction')
                ->setName('stations:streamers:add');

            $this->get('/delete/{id}/{csrf}', Controller\Stations\StreamersController::class.':deleteAction')
                ->setName('stations:streamers:delete');

        })->add([Middleware\Permissions::class, 'manage station streamers', true]);

        $this->group('/webhooks', function () {

            $this->get('', Controller\Stations\WebhooksController::class.':indexAction')
                ->setName('stations:webhooks:index');

            $this->map(['GET', 'POST'], '/edit/{id}', Controller\Stations\WebhooksController::class.':editAction')
                ->setName('stations:webhooks:edit');

            $this->map(['GET', 'POST'], '/add[/{type}]', Controller\Stations\WebhooksController::class.':addAction')
                ->setName('stations:webhooks:add');

            $this->get('/toggle/{id}/{csrf}', Controller\Stations\WebhooksController::class.':toggleAction')
                ->setName('stations:webhooks:toggle');

            $this->get('/test/{id}/{csrf}', Controller\Stations\WebhooksController::class.':testAction')
                ->setName('stations:webhooks:test');

            $this->get('/delete/{id}/{csrf}', Controller\Stations\WebhooksController::class.':deleteAction')
                ->setName('stations:webhooks:delete');

        })->add([Middleware\Permissions::class, 'manage station web hooks', true]);

        // END /stations GROUP

    })
        ->add(Middleware\Module\Stations::class)
        ->add([Middleware\Permissions::class, 'view station management', true])
        ->add(Middleware\GetStation::class)
        ->add(Middleware\EnableView::class)
        ->add(Middleware\RequireLogin::class);

};
