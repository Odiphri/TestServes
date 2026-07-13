<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\Concerns\AuthorizesPlatformSections;
use App\Models\SystemSetting;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SystemSettingController extends Controller
{
    use AuthorizesPlatformSections;

    public function index()
    {
        $this->requireSuperAdmin();

        return view('super-admin.system-settings.index', [
            'settings' => SystemSetting::values(),
            'sections' => $this->sections(),
        ]);
    }

    public function update(Request $request)
    {
        $this->requireSuperAdmin();

        $fields = $this->fields();
        $existing = SystemSetting::values();

        $data = $request->validate(collect($fields)->mapWithKeys(fn ($field, $key) => [
            $key => $this->rulesFor($field),
        ])->all());

        foreach ($fields as $key => $field) {
            $value = match ($field['type']) {
                'boolean' => $request->boolean($key) ? '1' : '0',
                'file' => $request->hasFile($key)
                    ? $request->file($key)->store('platform-logos', 'public')
                    : ($existing[$key] ?? null),
                'secret' => filled($data[$key] ?? null) ? $data[$key] : ($existing[$key] ?? null),
                default => $data[$key] ?? null,
            };

            if ($field['type'] === 'file' && $request->hasFile($key) && filled($existing[$key] ?? null)) {
                Storage::disk('public')->delete($existing[$key]);
            }

            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        PlatformActivity::log('system_settings_updated', 'Updated platform system settings.');

        return back()->with('success', 'System settings saved.');
    }

    private function fields(): array
    {
        return collect($this->sections())
            ->flatMap(fn ($section) => $section['fields'])
            ->all();
    }

    private function rulesFor(array $field): array
    {
        $base = ['nullable'];

        return match ($field['type']) {
            'boolean' => ['nullable', 'boolean'],
            'file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'email' => [...$base, 'email', 'max:255'],
            'url' => [...$base, 'url', 'max:255'],
            'integer' => [...$base, 'integer', 'min:0'],
            'color' => [...$base, 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'select' => [...$base, Rule::in(array_keys($field['options'] ?? []))],
            'textarea' => [...$base, 'string'],
            'secret' => [...$base, 'string', 'max:1000'],
            default => [...$base, 'string', 'max:255'],
        };
    }

    private function sections(): array
    {
        return [
            'business' => [
                'title' => 'Business Profile',
                'description' => 'Identity and contact information for the TestServes platform.',
                'fields' => [
                    'platform_name' => ['label' => 'Platform name', 'type' => 'text', 'placeholder' => 'TestServes'],
                    'platform_logo' => ['label' => 'Platform logo', 'type' => 'file'],
                    'contact_email' => ['label' => 'Support email', 'type' => 'email', 'placeholder' => 'support@testserves.com'],
                    'contact_phone' => ['label' => 'Support phone', 'type' => 'text', 'placeholder' => '+234...'],
                    'whatsapp_number' => ['label' => 'WhatsApp number', 'type' => 'text', 'placeholder' => '+234...'],
                    'support_phone' => ['label' => 'Public support phone', 'type' => 'text', 'placeholder' => '08083019506'],
                    'support_email' => ['label' => 'Public support email', 'type' => 'email', 'placeholder' => 'testserves.ng@gmail.com'],
                    'whatsapp_support_url' => ['label' => 'WhatsApp support URL', 'type' => 'url', 'placeholder' => 'https://wa.me/...'],
                    'whatsapp_community_url' => ['label' => 'WhatsApp community URL', 'type' => 'url', 'placeholder' => 'https://chat.whatsapp.com/...'],
                    'x_url' => ['label' => 'X account URL', 'type' => 'url', 'placeholder' => 'https://x.com/TestServesng'],
                    'x_handle' => ['label' => 'X handle', 'type' => 'text', 'placeholder' => '@TestServesng'],
                    'legal_operator_name' => ['label' => 'Legal operator name', 'type' => 'text', 'placeholder' => 'Big H Multidynamic Ventures'],
                    'legal_operator_statement' => ['label' => 'Legal operator statement', 'type' => 'textarea', 'placeholder' => \App\Support\PublicSiteSettings::OPERATOR_STATEMENT],
                    'company_registration_number' => ['label' => 'CAC registration number', 'type' => 'text', 'placeholder' => 'Only add a verified number'],
                    'company_registration_label' => ['label' => 'Registration label', 'type' => 'text', 'placeholder' => 'CAC registration number'],
                    'business_address' => ['label' => 'Business address', 'type' => 'textarea', 'placeholder' => 'Office address shown on invoices and contact pages'],
                ],
            ],
            'manual_payments' => [
                'title' => 'Manual Payments',
                'description' => 'Bank transfer details shown to schools before automatic payment is enabled.',
                'fields' => [
                    'bank_name' => ['label' => 'Bank name', 'type' => 'text', 'placeholder' => 'Access Bank'],
                    'account_name' => ['label' => 'Account name', 'type' => 'text', 'placeholder' => 'TestServes Limited'],
                    'account_number' => ['label' => 'Account number', 'type' => 'text', 'placeholder' => '0123456789'],
                    'manual_payment_instructions' => ['label' => 'Manual payment instructions', 'type' => 'textarea', 'placeholder' => 'Tell schools what to put as transfer narration and how approval works.'],
                    'manual_transfer_requires_approval' => ['label' => 'Manual transfer requires approval', 'type' => 'boolean'],
                ],
            ],
            'paystack' => [
                'title' => 'Paystack Setup',
                'description' => 'Save Paystack credentials here when you are ready. This page stores them; full automation/webhooks are still a future phase.',
                'fields' => [
                    'paystack_enabled' => ['label' => 'Enable Paystack checkout when automation is built', 'type' => 'boolean'],
                    'paystack_environment' => ['label' => 'Environment', 'type' => 'select', 'options' => ['test' => 'Test', 'live' => 'Live']],
                    'paystack_public_key' => ['label' => 'Public key', 'type' => 'text', 'placeholder' => 'pk_test_...'],
                    'paystack_secret_key' => ['label' => 'Secret key', 'type' => 'secret', 'placeholder' => 'Leave blank to keep current secret'],
                    'paystack_webhook_secret' => ['label' => 'Webhook secret/signature key', 'type' => 'secret', 'placeholder' => 'Leave blank to keep current secret'],
                    'paystack_callback_url' => ['label' => 'Callback URL', 'type' => 'url', 'placeholder' => url('/payments/paystack/callback')],
                    'auto_activate_paystack_payments' => ['label' => 'Auto-activate successful Paystack payments later', 'type' => 'boolean'],
                ],
            ],
            'subscriptions' => [
                'title' => 'Subscription Defaults',
                'description' => 'Default rules used when creating schools and subscriptions.',
                'fields' => [
                    'default_trial_days' => ['label' => 'Default trial days', 'type' => 'integer', 'placeholder' => '14'],
                    'default_grace_period_days' => ['label' => 'Grace period days', 'type' => 'integer', 'placeholder' => '7'],
                    'deactivated_school_delete_after_days' => ['label' => 'Deactivated school delete notice days', 'type' => 'integer', 'placeholder' => '30'],
                    'auto_suspend_expired_schools' => ['label' => 'Auto suspend expired schools later', 'type' => 'boolean'],
                ],
            ],
            'branding' => [
                'title' => 'Default Branding',
                'description' => 'Fallback colors for new portals before a school customizes its branding.',
                'fields' => [
                    'default_primary_color' => ['label' => 'Primary color', 'type' => 'color', 'default' => '#0f766e'],
                    'default_secondary_color' => ['label' => 'Secondary color', 'type' => 'color', 'default' => '#102033'],
                    'default_accent_color' => ['label' => 'Accent color', 'type' => 'color', 'default' => '#f59e0b'],
                ],
            ],
            'maintenance' => [
                'title' => 'Maintenance',
                'description' => 'Prepared controls for a future public maintenance banner or lock screen.',
                'fields' => [
                    'maintenance_mode' => ['label' => 'Maintenance mode placeholder', 'type' => 'boolean'],
                    'maintenance_message' => ['label' => 'Maintenance message', 'type' => 'textarea', 'placeholder' => 'We are upgrading TestServes. Please check back shortly.'],
                ],
            ],
        ];
    }

}
