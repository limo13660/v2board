<?php

use App\Services\ThemeService;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (Request $request) {
    if (config('daotech.app_url') && config('daotech.safe_mode_enable', 0)) {
        if ($request->server('HTTP_HOST') !== parse_url(config('daotech.app_url'))['host']) {
            abort(403);
        }
    }
    $renderParams = [
        'title' => config('daotech.app_name', 'DaoTech'),
        'theme' => config('daotech.frontend_theme', 'default'),
        'version' => config('app.version'),
        'description' => config('daotech.app_description', 'DaoTech is best'),
        'logo' => config('daotech.logo')
    ];

    if (!config("theme.{$renderParams['theme']}")) {
        $themeService = new ThemeService($renderParams['theme']);
        $themeService->init();
    }

    $renderParams['theme_config'] = config('theme.' . config('daotech.frontend_theme', 'default'));
    return view('theme::' . config('daotech.frontend_theme', 'default') . '.dashboard', $renderParams);
});

//TODO:: 兼容
Route::get('/' . config('daotech.secure_path', config('daotech.frontend_admin_path', hash('crc32b', config('app.key')))), function () {
    return view('admin', [
        'title' => config('daotech.app_name', 'DaoTech'),
        'theme_sidebar' => config('daotech.frontend_theme_sidebar', 'light'),
        'theme_header' => config('daotech.frontend_theme_header', 'dark'),
        'theme_color' => config('daotech.frontend_theme_color', 'default'),
        'background_url' => config('daotech.frontend_background_url'),
        'version' => config('app.version'),
        'logo' => config('daotech.logo'),
        'secure_path' => config('daotech.secure_path', config('daotech.frontend_admin_path', hash('crc32b', config('app.key'))))
    ]);
});

if (!empty(config('daotech.subscribe_path'))) {
    Route::get(config('daotech.subscribe_path'), 'V1\\Client\\ClientController@subscribe')->middleware('client');
}