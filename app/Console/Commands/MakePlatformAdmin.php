<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MakePlatformAdmin extends Command
{
    protected $signature = 'platform:make-admin
        {email : Platform admin email}
        {--name=Super Admin : Platform admin name}
        {--password= : Password to set, otherwise you will be prompted}
        {--role=super_admin : super_admin, sales_admin, support_admin, finance_admin, or operations_admin}';

    protected $description = 'Create or update a TestServes platform admin account.';

    public function handle(): int
    {
        $password = $this->option('password') ?: $this->secret('Password');

        $data = [
            'email' => $this->argument('email'),
            'name' => $this->option('name'),
            'password' => $password,
            'role' => $this->option('role'),
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['super_admin', 'sales_admin', 'support_admin', 'finance_admin', 'operations_admin'])],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        PlatformAdmin::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => $data['password'],
                'role' => $data['role'],
                'is_active' => true,
            ]
        );

        $this->info("Platform admin {$data['email']} is ready.");

        return self::SUCCESS;
    }
}
