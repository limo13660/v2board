<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use App\Utils\Dict;
use Illuminate\Support\Facades\Http;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'tos_url' => config('daotech.tos_url'),
                'is_email_verify' => (int)config('daotech.email_verify', 0) ? 1 : 0,
                'is_invite_force' => (int)config('daotech.invite_force', 0) ? 1 : 0,
                'email_whitelist_suffix' => (int)config('daotech.email_whitelist_enable', 0)
                    ? $this->getEmailSuffix()
                    : 0,
                'is_recaptcha' => (int)config('daotech.recaptcha_enable', 0) ? 1 : 0,
                'recaptcha_site_key' => config('daotech.recaptcha_site_key'),
                'app_description' => config('daotech.app_description'),
                'app_url' => config('daotech.app_url'),
                'logo' => config('daotech.logo'),
            ]
        ]);
    }

    private function getEmailSuffix()
    {
        $suffix = config('daotech.email_whitelist_suffix', Dict::EMAIL_WHITELIST_SUFFIX_DEFAULT);
        if (!is_array($suffix)) {
            return preg_split('/,/', $suffix);
        }
        return $suffix;
    }
}
