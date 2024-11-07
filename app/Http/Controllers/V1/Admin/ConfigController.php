<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfigSave;
use App\Jobs\SendEmailJob;
use App\Services\TelegramService;
use App\Utils\Dict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ConfigController extends Controller
{
    public function getEmailTemplate()
    {
        $path = resource_path('views/mail/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        return response([
            'data' => $files
        ]);
    }

    public function getThemeTemplate()
    {
        $path = public_path('theme/');
        $files = array_map(function ($item) use ($path) {
            return str_replace($path, '', $item);
        }, glob($path . '*'));
        return response([
            'data' => $files
        ]);
    }

    public function testSendMail(Request $request)
    {
        $obj = new SendEmailJob([
            'email' => $request->user['email'],
            'subject' => 'This is daotech test email',
            'template_name' => 'notify',
            'template_value' => [
                'name' => config('daotech.app_name', 'DaoTech'),
                'content' => 'This is daotech test email',
                'url' => config('daotech.app_url')
            ]
        ]);
        return response([
            'data' => true,
            'log' => $obj->handle()
        ]);
    }

    public function setTelegramWebhook(Request $request)
    {
        $hookUrl = secure_url('/api/v1/guest/telegram/webhook?access_token=' . md5(config('daotech.telegram_bot_token', $request->input('telegram_bot_token'))));
        $telegramService = new TelegramService($request->input('telegram_bot_token'));
        $telegramService->getMe();
        $telegramService->setWebhook($hookUrl);
        return response([
            'data' => true
        ]);
    }

    public function fetch(Request $request)
    {
        $key = $request->input('key');
        $data = [
            'deposit' => [
                'deposit_bounus' => config('daotech.deposit_bounus', [])
            ],
            'invite' => [
                'invite_force' => (int)config('daotech.invite_force', 0),
                'invite_commission' => config('daotech.invite_commission', 10),
                'invite_gen_limit' => config('daotech.invite_gen_limit', 5),
                'invite_never_expire' => config('daotech.invite_never_expire', 0),
                'commission_first_time_enable' => config('daotech.commission_first_time_enable', 1),
                'commission_auto_check_enable' => config('daotech.commission_auto_check_enable', 1),
                'commission_withdraw_limit' => config('daotech.commission_withdraw_limit', 100),
                'commission_withdraw_method' => config('daotech.commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close_enable' => config('daotech.withdraw_close_enable', 0),
                'commission_distribution_enable' => config('daotech.commission_distribution_enable', 0),
                'commission_distribution_l1' => config('daotech.commission_distribution_l1'),
                'commission_distribution_l2' => config('daotech.commission_distribution_l2'),
                'commission_distribution_l3' => config('daotech.commission_distribution_l3')
            ],
            'site' => [
                'logo' => config('daotech.logo'),
                'force_https' => (int)config('daotech.force_https', 0),
                'stop_register' => (int)config('daotech.stop_register', 0),
                'app_name' => config('daotech.app_name', 'DaoTech'),
                'app_description' => config('daotech.app_description', 'DaoTech is best!'),
                'app_url' => config('daotech.app_url'),
                'subscribe_url' => config('daotech.subscribe_url'),
                'subscribe_path' => config('daotech.subscribe_path'),
                'try_out_plan_id' => (int)config('daotech.try_out_plan_id', 0),
                'try_out_hour' => (int)config('daotech.try_out_hour', 1),
                'tos_url' => config('daotech.tos_url'),
                'currency' => config('daotech.currency', 'CNY'),
                'currency_symbol' => config('daotech.currency_symbol', '¥'),
            ],
            'subscribe' => [
                'plan_change_enable' => (int)config('daotech.plan_change_enable', 1),
                'reset_traffic_method' => (int)config('daotech.reset_traffic_method', 0),
                'surplus_enable' => (int)config('daotech.surplus_enable', 1),
                'new_order_event_id' => (int)config('daotech.new_order_event_id', 0),
                'renew_order_event_id' => (int)config('daotech.renew_order_event_id', 0),
                'change_order_event_id' => (int)config('daotech.change_order_event_id', 0),
                'show_info_to_server_enable' => (int)config('daotech.show_info_to_server_enable', 0)
            ],
            'frontend' => [
                'frontend_theme' => config('daotech.frontend_theme', 'daotech'),
                'frontend_theme_sidebar' => config('daotech.frontend_theme_sidebar', 'light'),
                'frontend_theme_header' => config('daotech.frontend_theme_header', 'dark'),
                'frontend_theme_color' => config('daotech.frontend_theme_color', 'default'),
                'frontend_background_url' => config('daotech.frontend_background_url'),
            ],
            'server' => [
                'server_token' => config('daotech.server_token'),
                'server_pull_interval' => config('daotech.server_pull_interval', 60),
                'server_push_interval' => config('daotech.server_push_interval', 60),
                'device_limit_mode' => config('daotech.device_limit_mode', 0)
            ],
            'email' => [
                'email_template' => config('daotech.email_template', 'default'),
                'email_host' => config('daotech.email_host'),
                'email_port' => config('daotech.email_port'),
                'email_username' => config('daotech.email_username'),
                'email_password' => config('daotech.email_password'),
                'email_encryption' => config('daotech.email_encryption'),
                'email_from_address' => config('daotech.email_from_address')
            ],
            'telegram' => [
                'telegram_bot_enable' => config('daotech.telegram_bot_enable', 0),
                'telegram_bot_token' => config('daotech.telegram_bot_token'),
                'telegram_discuss_link' => config('daotech.telegram_discuss_link')
            ],
            'app' => [
                'windows_version' => config('daotech.windows_version'),
                'windows_download_url' => config('daotech.windows_download_url'),
                'macos_version' => config('daotech.macos_version'),
                'macos_download_url' => config('daotech.macos_download_url'),
                'android_version' => config('daotech.android_version'),
                'android_download_url' => config('daotech.android_download_url')
            ],
            'safe' => [
                'email_verify' => (int)config('daotech.email_verify', 0),
                'safe_mode_enable' => (int)config('daotech.safe_mode_enable', 0),
                'secure_path' => config('daotech.secure_path', config('daotech.frontend_admin_path', hash('crc32b', config('app.key')))),
                'email_whitelist_enable' => (int)config('daotech.email_whitelist_enable', 0),
                'email_whitelist_suffix' => config('daotech.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT),
                'email_gmail_limit_enable' => config('daotech.email_gmail_limit_enable', 0),
                'recaptcha_enable' => (int)config('daotech.recaptcha_enable', 0),
                'recaptcha_key' => config('daotech.recaptcha_key'),
                'recaptcha_site_key' => config('daotech.recaptcha_site_key'),
                'register_limit_by_ip_enable' => (int)config('daotech.register_limit_by_ip_enable', 0),
                'register_limit_count' => config('daotech.register_limit_count', 3),
                'register_limit_expire' => config('daotech.register_limit_expire', 60),
                'password_limit_enable' => (int)config('daotech.password_limit_enable', 1),
                'password_limit_count' => config('daotech.password_limit_count', 5),
                'password_limit_expire' => config('daotech.password_limit_expire', 60)
            ]
        ];
        if ($key && isset($data[$key])) {
            return response([
                'data' => [
                    $key => $data[$key]
                ]
            ]);
        };
        // TODO: default should be in Dict
        return response([
            'data' => $data
        ]);
    }

    public function save(ConfigSave $request)
    {
        $data = $request->validated();
        $config = config('daotech');
        foreach (ConfigSave::RULES as $k => $v) {
            if (!in_array($k, array_keys(ConfigSave::RULES))) {
                unset($config[$k]);
                continue;
            }
            if (array_key_exists($k, $data)) {
                $config[$k] = $data[$k];
            }
        }
        $data = var_export($config, 1);
        if (!File::put(base_path() . '/config/daotech.php', "<?php\n return $data ;")) {
            abort(500, '修改失败');
        }
        if (function_exists('opcache_reset')) {
            if (opcache_reset() === false) {
                abort(500, '缓存清除失败，请卸载或检查opcache配置状态');
            }
        }
        Artisan::call('config:cache');
        if(Cache::has('WEBMANPID')) {
            $pid = Cache::get('WEBMANPID');
            Cache::forget('WEBMANPID');
            return response([
                'data' => posix_kill($pid, 15)
            ]);
        }
        return response([
            'data' => true
        ]);
    }
}
